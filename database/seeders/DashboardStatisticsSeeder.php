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
            return $created;
        });
        $this->command?->info("Pedidos creados: {$created}. Prefijo DEMO-STATS-. Sin cobros ni cambios de inventario.");
    }
}
