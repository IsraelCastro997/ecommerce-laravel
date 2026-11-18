<?php

namespace App\Payments;

use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookReceived;
use RuntimeException;

class StripeWebhookHandler
{
    public function handle(WebhookReceived $event): void
    {
        $payload = $event->payload;

        if (!in_array($payload['type'] ?? '', [
            'checkout.session.completed',
            'checkout.session.expired',
        ], true)) {
            return;
        }

        $object = $payload['data']['object'] ?? [];

        // Ignora sesiones ajenas a este flujo.
        if (empty($object['metadata']['order_id'])) {
            return;
        }

        $session = Cashier::stripe()->checkout->sessions->retrieve(
            $object['id'],
            [],
        );

        $paid = $session->payment_status === 'paid';
        $expired = $session->status === 'expired';

        if (!$paid && !$expired) {
            return;
        }

        DB::transaction(function () use ($payload, $session, $paid) {
            $order = DB::table('orders')
                ->where('id', $session->metadata->order_id)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                throw new RuntimeException('No existe el pedido de Stripe.');
            }

            $payment = DB::table('payment_transactions')
                ->where('order_id', $order->id)
                ->where('stripe_checkout_session_id', $session->id)
                ->lockForUpdate()
                ->first();

            // Stripe puede notificar antes de guardar la sesión localmente.
            // Lanzar una excepción permite que vuelva a entregar el evento.
            if (!$payment) {
                throw new RuntimeException('La sesión todavía no está registrada.');
            }

            if (
                strtoupper($session->currency) !== $order->currency
                || (string) $session->amount_total !== bcmul(
                    (string) $order->grand_total,
                    '100',
                    0,
                )
            ) {
                throw new RuntimeException('Importe o moneda inconsistentes.');
            }

            if (DB::table('stripe_events')
                ->where('stripe_event_id', $payload['id'])
                ->where('status', 'processed')
                ->exists()) {
                return;
            }

            // El pedido bloqueado impide consumir existencias dos veces.
            if ($order->status === 'pending_payment') {
                $itemIds = DB::table('order_items')
                    ->where('order_id', $order->id)
                    ->pluck('id');

                $reservations = DB::table('stock_reservations')
                    ->whereIn('order_item_id', $itemIds)
                    ->where('status', 'held')
                    ->orderBy('product_id')
                    ->orderBy('warehouse_id')
                    ->lockForUpdate()
                    ->get();

                $expectedQuantity = (int) DB::table('order_items')
                    ->where('order_id', $order->id)
                    ->sum('quantity');

                if ((int) $reservations->sum('quantity') !== $expectedQuantity) {
                    throw new RuntimeException('Las reservas del pedido no coinciden.');
                }

                foreach ($reservations as $reservation) {
                    $balance = DB::table('inventory_balances')
                        ->where('product_id', $reservation->product_id)
                        ->where('warehouse_id', $reservation->warehouse_id)
                        ->lockForUpdate()
                        ->first();

                    if (
                        !$balance
                        || $balance->qty_reserved < $reservation->quantity
                        || ($paid && $balance->qty_on_hand < $reservation->quantity)
                    ) {
                        throw new RuntimeException('Existencias inconsistentes.');
                    }

                    DB::table('inventory_balances')
                        ->where('id', $balance->id)
                        ->update([
                            'qty_reserved' => $balance->qty_reserved
                                - $reservation->quantity,
                            'qty_on_hand' => $paid
                                ? $balance->qty_on_hand - $reservation->quantity
                                : $balance->qty_on_hand,
                            'updated_at' => now(),
                        ]);

                    DB::table('stock_reservations')
                        ->where('id', $reservation->id)
                        ->update([
                            'status' => $paid ? 'consumed' : 'released',
                            'updated_at' => now(),
                        ]);
                }

                DB::table('orders')->where('id', $order->id)->update([
                    'status' => $paid ? 'paid' : 'expired',
                    'paid_at' => $paid ? now() : null,
                    'updated_at' => now(),
                ]);
            } elseif (
                ($paid && $order->status !== 'paid')
                || (!$paid && $order->status !== 'expired')
            ) {
                throw new RuntimeException('El estado del pedido requiere revisión.');
            }

            DB::table('payment_transactions')
                ->where('id', $payment->id)
                ->update([
                    'status' => $paid ? 'paid' : 'expired',
                    'stripe_payment_intent_id' => $session->payment_intent,
                    'updated_at' => now(),
                ]);

            DB::table('stripe_events')->insert([
                'stripe_event_id' => $payload['id'],
                'event_type' => $payload['type'],
                'payload_sha256' => hash(
                    'sha256',
                    json_encode($payload, JSON_THROW_ON_ERROR),
                ),
                'status' => 'processed',
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, 3);
    }
}