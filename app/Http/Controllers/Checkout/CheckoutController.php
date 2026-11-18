<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Cashier\Cashier;
use Stripe\Exception\ApiErrorException;

class CheckoutController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:254'],
            'currency' => ['required', 'in:MXN,USD'],
            'idempotency_key' => ['required', 'uuid'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => [
                'required', 'integer', 'min:1', 'distinct',
            ],
            'items.*.quantity' => [
                'required', 'integer', 'min:1', 'max:99',
            ],
        ]);

        $items = collect($data['items'])
            ->map(fn (array $item) => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (int) $item['quantity'],
            ])
            ->sortBy('product_id')
            ->values();

        $order = DB::transaction(function () use ($request, $data, $items) {
            // Serializa los intentos de checkout del mismo usuario.
            DB::table('users')
                ->where('id', $request->user()->id)
                ->lockForUpdate()
                ->first();

            $existing = Order::where(
                'idempotency_key',
                $data['idempotency_key'],
            )->first();

            if ($existing) {
                abort_unless(
                    (int) $existing->user_id === (int) $request->user()->id,
                    403,
                );

                $savedItems = $existing->items()
                    ->orderBy('product_id')
                    ->get()
                    ->map(fn ($item) => [
                        'product_id' => (int) $item->product_id,
                        'quantity' => (int) $item->quantity,
                    ])
                    ->values();

                abort_unless(
                    $existing->currency === $data['currency']
                    && $existing->customer_email === $data['email']
                    && $savedItems->all() === $items->all(),
                    409,
                    'Este intento pertenece a otro contenido de compra.',
                );

                return $existing;
            }

            $publication = DB::table('price_publications')
                ->join('markets', 'markets.id', '=', 'price_publications.market_id')
                ->where('markets.code', 'MX')
                ->where('price_publications.status', 'published')
                ->where('price_publications.is_active', true)
                ->orderByDesc('price_publications.id')
                ->select('price_publications.*')
                ->first();

            if (!$publication) {
                $this->reject('No hay una publicación de precios activa.');
            }

            $products = Product::whereIn(
                'id',
                $items->pluck('product_id'),
            )->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            $prices = DB::table('product_price_snapshots')
                ->where('price_publication_id', $publication->id)
                ->whereIn('product_id', $items->pluck('product_id'))
                ->get()
                ->keyBy('product_id');

            $lines = [];
            $subtotal = 0;

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                $price = $prices->get($item['product_id']);

                if (
                    !$product || !$price || $product->status !== 'active'
                    || ($product->available_from && $product->available_from->isFuture())
                    || ($product->available_until && $product->available_until->isPast())
                ) {
                    $this->reject('Un producto no está disponible para comprar.');
                }

                $field = $data['currency'] === 'MXN'
                    ? 'price_mxn'
                    : 'price_usd';

                $unit = (int) bcmul((string) $price->{$field}, '100', 0);

                if ($unit < 1 || $unit > 99_999_999) {
                    $this->reject('Un producto tiene un precio fuera del límite del prototipo.');
                }

                $balances = DB::table('inventory_balances')
                    ->where('product_id', $product->id)
                    ->orderBy('warehouse_id')
                    ->lockForUpdate()
                    ->get();

                $available = $balances->sum(
                    fn ($balance) => max(
                        0,
                        $balance->qty_on_hand - $balance->qty_reserved,
                    ),
                );

                if ($available < $item['quantity']) {
                    $this->reject("Stock insuficiente para {$product->name}.");
                }

                $lineSubtotal = $unit * $item['quantity'];
                $subtotal += $lineSubtotal;

                $lines[] = compact(
                    'product', 'item', 'unit', 'lineSubtotal', 'balances',
                );
            }

            // IVA del 16 %, redondeado a centavos.
            $tax = intdiv($subtotal * 16 + 50, 100);
            $total = $subtotal + $tax;

            if ($total > 99_999_999) {
                $this->reject('El pedido supera el límite de este prototipo.');
            }

            $order = Order::forceCreate([
                'order_number' => 'RF-'.Str::upper(Str::random(16)),
                'user_id' => $request->user()->id,
                'customer_email' => $data['email'],
                'idempotency_key' => $data['idempotency_key'],
                'status' => 'pending_payment',
                'currency' => $data['currency'],
                'subtotal' => $this->decimal($subtotal),
                'tax_total' => $this->decimal($tax),
                'discount_total' => '0.00',
                'shipping_total' => '0.00',
                'grand_total' => $this->decimal($total),
                'price_publication_id' => $publication->id,
                'exchange_rate_id' => $publication->exchange_rate_id,
                'exchange_rate_value' => $publication->exchange_rate_id
                    ? DB::table('exchange_rates')
                        ->where('id', $publication->exchange_rate_id)
                        ->value('rate')
                    : null,
            ]);

            $accumulatedSubtotal = 0;
            $allocatedTax = 0;

            foreach ($lines as $line) {
                $accumulatedSubtotal += $line['lineSubtotal'];

                $nextTax = intdiv($accumulatedSubtotal * 16 + 50, 100);
                $lineTax = $nextTax - $allocatedTax;
                $allocatedTax = $nextTax;

                $orderItemId = DB::table('order_items')->insertGetId([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'sku_snapshot' => $line['product']->sku,
                    'name_snapshot' => $line['product']->name,
                    'quantity' => $line['item']['quantity'],
                    'unit_price' => $this->decimal($line['unit']),
                    'tax_total' => $this->decimal($lineTax),
                    'discount_total' => '0.00',
                    'line_total' => $this->decimal(
                        $line['lineSubtotal'] + $lineTax,
                    ),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $remaining = $line['item']['quantity'];

                foreach ($line['balances'] as $balance) {
                    $quantity = min(
                        $remaining,
                        max(0, $balance->qty_on_hand - $balance->qty_reserved),
                    );

                    if ($quantity === 0) {
                        continue;
                    }

                    DB::table('inventory_balances')
                        ->where('id', $balance->id)
                        ->increment('qty_reserved', $quantity, [
                            'updated_at' => now(),
                        ]);

                    DB::table('stock_reservations')->insert([
                        'operation_id' => (string) Str::uuid(),
                        'order_item_id' => $orderItemId,
                        'product_id' => $line['product']->id,
                        'warehouse_id' => $balance->warehouse_id,
                        'quantity' => $quantity,
                        'status' => 'held',
                        'expires_at' => $order->created_at->copy()->addDay(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $remaining -= $quantity;

                    if ($remaining === 0) {
                        break;
                    }
                }
            }

            return $order;
        }, 3);

        if ($order->status === 'paid') {
            return response()->json([
                'message' => 'Este pedido ya está pagado.',
            ], 409);
        }

        abort_unless(
            $order->status === 'pending_payment',
            409,
            'Este pedido ya no admite pagos.',
        );

        try {
            $payment = DB::table('payment_transactions')
                ->where('order_id', $order->id)
                ->whereNotNull('stripe_checkout_session_id')
                ->first();

            if ($payment) {
                $session = Cashier::stripe()->checkout->sessions->retrieve(
                    $payment->stripe_checkout_session_id,
                    [],
                );
            } else {
                // Una expiración estable permite reintentos idempotentes.
                $expiresAt = $order->created_at->copy()->addDay()->timestamp;

                abort_if(
                    $expiresAt < now()->addMinutes(31)->timestamp,
                    409,
                    'El intento requiere reconciliación antes de volver a pagar.',
                );

                $session = Cashier::stripe()->checkout->sessions->create([
                    'mode' => 'payment',
                    'payment_method_types' => ['card'],
                    'customer_email' => $order->customer_email,
                    'client_reference_id' => (string) $order->id,
                    'expires_at' => $expiresAt,

                    // Aquí va automatic_tax, dentro de las opciones de Checkout.
                    'automatic_tax' => ['enabled' => false],

                    'line_items' => [[
                        'quantity' => 1,
                        'price_data' => [
                            'currency' => strtolower($order->currency),
                            'unit_amount' => (int) bcmul(
                                $order->grand_total,
                                '100',
                                0,
                            ),
                            'product_data' => [
                                'name' => 'Pedido '.$order->order_number.' — IVA incluido',
                            ],
                        ],
                    ]],

                    'metadata' => [
                        'order_id' => (string) $order->id,
                    ],
                    'success_url' => route('store.checkout.success')
                        .'?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('store.checkout.cancel'),
                ], [
                    'idempotency_key' => $order->idempotency_key,
                ]);

                // El índice único de la sesión evita duplicados en reintentos.
                DB::table('payment_transactions')->upsert([[
                    'order_id' => $order->id,
                    'provider' => 'stripe',
                    'status' => 'pending',
                    'stripe_checkout_session_id' => $session->id,
                    'currency' => $order->currency,
                    'amount' => $order->grand_total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]], ['stripe_checkout_session_id'], ['updated_at']);
            }
        } catch (ApiErrorException $exception) {
            report($exception);

            // Un error de red no demuestra que Stripe no haya creado la sesión.
            // Se conserva el pedido y su clave para reintentar sin duplicarlo.
            return response()->json([
                'message' => 'No se pudo abrir Stripe. Reintenta el mismo pago.',
            ], 503);
        }

        if ($session->status !== 'open' || !$session->url) {
            return response()->json([
                'message' => 'La sesión terminó. Consulta el estado del pedido.',
            ], 409);
        }

        return response()->json([
            'checkout_url' => $session->url,
        ]);
    }

    private function decimal(int $cents): string
    {
        return bcdiv((string) $cents, '100', 2);
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages([
            'items' => $message,
        ]);
    }
}