<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DashboardStatisticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!app()->environment('local')) {
            throw new \RuntimeException('Este seeder solo se ejecuta en local.');
        }
        $products = Product::where('status', 'active')->whereHas('priceSnapshots')
            ->with(['priceSnapshots' => fn ($q) => $q->orderByDesc('price_publication_id')])
            ->orderBy('id')->limit(6)->get();
        if ($products->count() < 3) {
            throw new \RuntimeException('Se requieren tres productos activos con precios.');
        }
        $created = DB::transaction(function () use ($products) {
            $created = 0;
            for ($i = 1; $i <= 36; $i++) {
                $number = sprintf('DEMO-STATS-%03d', $i);
                if (Order::where('order_number', $number)->exists()) {
                    continue;
                }
                $currency = $i % 3 === 0 ? 'USD' : 'MXN';
                $date = now()->subDays(($i - 1) % 30);
                $subtotal = $tax = 0;
                $lines = [];
                foreach ($products as $position => $product) {
                    if ($position > 0 && $i % ($position + 1) !== 0) {
                        continue;
                    }
                    $price = $product->priceSnapshots->first();
                    $unit = (int) bcmul((string) $price->{$currency === 'MXN' ? 'price_mxn' : 'price_usd'}, '100', 0);
                    $quantity = 1 + $i % 3;
                    $lineSubtotal = $unit * $quantity;
                    $subtotal += $lineSubtotal;
                    $nextTax = intdiv($subtotal * 16 + 50, 100);
                    $lineTax = $nextTax - $tax;
                    $tax = $nextTax;
                    $lines[] = [
                        'product_id' => $product->id,
                        'sku_snapshot' => $product->sku,
                        'name_snapshot' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => bcdiv((string) $unit, '100', 2),
                        'price_mxn' => $price->price_mxn,
                        'price_usd' => $price->price_usd,
                        // Demo: costo total del renglón al 65 % y tipo de cambio ficticio de 20 MXN/USD.
                        'allocated_cost_mxn' => bcmul(bcmul(bcdiv((string) $lineSubtotal, '100', 4), $currency === 'USD' ? '20' : '1', 4), '0.65', 4),
                        'tax_total' => bcdiv((string) $lineTax, '100', 2),
                        'discount_total' => '0.00',
                        'line_total' => bcdiv((string) ($lineSubtotal + $lineTax), '100', 2),
                        'created_at' => $date, 'updated_at' => $date,
                    ];
                }
                // Historial ficticio para estadísticas: no cobra ni reserva inventario.
                $order = Order::forceCreate([
                    'order_number' => $number,
                    'idempotency_key' => (string) Str::uuid(),
                    'customer_email' => 'estadisticas.demo@example.test',
                    'status' => $i <= 30 ? 'paid' : 'pending_payment',
                    'currency' => $currency,
                    'exchange_rate_value' => '20.00000000',
                    'subtotal' => bcdiv((string) $subtotal, '100', 2),
                    'tax_total' => bcdiv((string) $tax, '100', 2),
                    'discount_total' => '0.00', 'shipping_total' => '0.00',
                    'grand_total' => bcdiv((string) ($subtotal + $tax), '100', 2),
                    'paid_at' => $i <= 30 ? $date : null,
                    'created_at' => $date, 'updated_at' => $date,
                ]);
                foreach ($lines as $line) {
                    OrderItem::forceCreate(['order_id' => $order->id] + $line);
                }
                $created++;
            }
            // Completa solo los registros de esta muestra creados en ejecuciones anteriores.
            $demoOrders = Order::where('order_number', 'like', 'DEMO-STATS-%')
                ->where('customer_email', 'estadisticas.demo@example.test')->with('items')->get();
            foreach ($demoOrders as $order) {
                if ($order->exchange_rate_value === null || ($order->currency === 'MXN' && bccomp($order->exchange_rate_value, '1', 8) === 0)) {
                    $order->exchange_rate_value = '20.00000000';
                    $order->save();
                }
                foreach ($order->items as $item) {
                    // Precios históricos de la muestra, con el mismo tipo de cambio ficticio.
                    $item->price_mxn = $order->currency === 'MXN' ? $item->unit_price : bcmul($item->unit_price, '20', 2);
                    $item->price_usd = $order->currency === 'USD' ? $item->unit_price : bcdiv($item->unit_price, '20', 2);
                    $item->save();
                    if ($item->allocated_cost_mxn !== null) continue;
                    $net = bcsub(bcmul($item->unit_price, (string) $item->quantity, 4), $item->discount_total, 4);
                    $item->allocated_cost_mxn = bcmul(bcmul($net, $order->currency === 'USD' ? '20' : '1', 4), '0.65', 4);
                    $item->save();
                }
            }
            return $created;
        });
        $this->command?->info("Pedidos creados: {$created}. Prefijo DEMO-STATS-. Sin cobros ni cambios de inventario.");
    }
}
