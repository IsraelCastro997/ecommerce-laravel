<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Synthetic load-test dataset. NEVER run against production or a populated catalogue. */
final class MillionProductSeeder extends Seeder
{
    public function run(): void
    {
        $environment = app()->environment();
        if (! in_array($environment, ['local', 'testing'], true)) {
            throw new RuntimeException('Synthetic seeding is restricted to local/testing.');
        }
        if (DB::table('products')->exists()) {
            throw new RuntimeException('Products table must be empty for this deterministic fixture.');
        }

        $total = (int) env('CATALOG_SEED_COUNT', 300000);
        $batchSize = (int) env('CATALOG_SEED_CHUNK', 500);
        if (! in_array($total, [300000, 1000000], true) || $batchSize < 100 || $batchSize > 1000) {
            throw new RuntimeException('Use 300000 or 1000000 products and chunk 100..1000.');
        }

        $now = now();
        DB::table('currencies')->insert([
            ['code' => 'MXN', 'name' => 'Peso mexicano', 'minor_units' => 2],
            ['code' => 'USD', 'name' => 'Dólar estadounidense', 'minor_units' => 2],
        ]);
        DB::table('markets')->insert(['id' => 1, 'code' => 'MX', 'primary_currency' => 'MXN', 'name' => 'México']);
        DB::table('warehouses')->insert(['id' => 1, 'code' => 'MAIN', 'name' => 'Almacén principal', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('exchange_rates')->insert([
            'id' => 1, 'base_currency' => 'USD', 'quote_currency' => 'MXN', 'rate' => '18.00000000',
            'source' => 'SYNTHETIC', 'series_id' => 'TEST_ONLY', 'effective_date' => $now->toDateString(),
            'fetched_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('price_publications')->insert([
            'id' => 1, 'market_id' => 1, 'exchange_rate_id' => 1, 'status' => 'published',
            'is_active' => true, 'expected_products' => $total, 'processed_products' => $total,
            'published_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $categories = [];
        for ($i = 1; $i <= 50; $i++) {
            $categories[] = ['id' => $i, 'name' => 'Categoría demo '.$i, 'slug' => 'categoria-demo-'.$i,
                'active' => true, 'created_at' => $now, 'updated_at' => $now];
        }
        DB::table('categories')->insert($categories);
        $brands = [];
        for ($i = 1; $i <= 100; $i++) {
            $brands[] = ['id' => $i, 'name' => 'Marca demo '.$i, 'slug' => 'marca-demo-'.$i,
                'created_at' => $now, 'updated_at' => $now];
        }
        DB::table('brands')->insert($brands);
        \Spatie\Permission\Models\Role::findOrCreate('customer', 'web');
        \Spatie\Permission\Models\Role::findOrCreate('admin', 'web');
        $suppliers = [];
        for ($i = 1; $i <= 40; $i++) {
            $suppliers[] = ['id' => $i, 'name' => 'Proveedor demo '.$i,
                'country_code' => $i % 2 === 0 ? 'MX' : 'US', 'created_at' => $now, 'updated_at' => $now];
        }
        DB::table('suppliers')->insert($suppliers);

        for ($start = 1; $start <= $total; $start += $batchSize) {
            $end = min($start + $batchSize - 1, $total);
            $products = $pivot = $supplied = $balances = $lots = $prices = $snapshots = [];
            for ($i = $start; $i <= $end; $i++) {
                $category = (($i - 1) % 50) + 1;
                $brand = (($i - 1) % 100) + 1;
                $supplier = (($i - 1) % 40) + 1;
                $isUsd = $i % 3 === 0;
                $cost = 50 + ($i % 5000) / 10;
                $costMxn = $isUsd ? $cost * 18 : $cost;
                $qty = $i % 17 === 0 ? 0 : 5 + ($i % 60);
                $saleMxn = round($costMxn * 1.30, 2);
                $saleUsd = round($saleMxn / 18, 2);
                $products[] = [
                    'id' => $i, 'sku' => sprintf('DEMO-%08d', $i), 'slug' => 'producto-demo-'.$i,
                    'name' => 'Producto de demostración '.$i, 'description' => 'Producto sintético para pruebas de carga categoría '.$category,
                    'brand_id' => $brand, 'primary_category_id' => $category,
                    'origin_country' => $i % 4 === 0 ? 'US' : 'MX',
                    'status' => 'active', 'version' => 1, 'created_at' => $now, 'updated_at' => $now,
                ];
                $pivot[] = ['product_id' => $i, 'category_id' => $category];
                $supplied[] = [
                    'supplier_id' => $supplier, 'product_id' => $i, 'supplier_sku' => 'SUP-'.$i,
                    'purchase_currency' => $isUsd ? 'USD' : 'MXN', 'quoted_unit_cost' => round($cost, 4),
                    'lead_time_days' => 7, 'is_preferred' => true, 'created_at' => $now, 'updated_at' => $now,
                ];
                $balances[] = [
                    'product_id' => $i, 'warehouse_id' => 1, 'qty_on_hand' => $qty, 'qty_reserved' => 0,
                    'version' => 1, 'created_at' => $now, 'updated_at' => $now,
                ];
                if ($qty > 0) {
                    $lots[] = [
                        'product_id' => $i, 'warehouse_id' => 1, 'receipt_reference' => 'SEED-'.$i,
                        'quantity_received' => $qty, 'quantity_remaining' => $qty,
                        'purchase_currency' => $isUsd ? 'USD' : 'MXN', 'unit_cost_original' => round($cost, 4),
                        'acquisition_fx_rate' => $isUsd ? '18.00000000' : '1.00000000',
                        'unit_landed_cost_mxn' => round($costMxn, 4), 'received_at' => $now,
                        'created_at' => $now, 'updated_at' => $now,
                    ];
                }
                $prices[] = [
                    'product_id' => $i, 'market_id' => 1, 'base_currency' => $isUsd ? 'USD' : 'MXN',
                    'base_price' => $isUsd ? round($cost * 1.30, 4) : round($saleMxn, 4),
                    'pricing_policy' => 'protected_dynamic', 'markup_percent' => '30.0000',
                    'min_margin_percent' => '10.0000', 'fx_buffer_percent' => '0.0000',
                    'rounding_rule' => 'cent', 'enabled' => true, 'created_at' => $now, 'updated_at' => $now,
                ];
                $snapshots[] = [
                    'product_id' => $i, 'price_publication_id' => 1,
                    'price_mxn' => $saleMxn, 'price_usd' => $saleUsd,
                    'historical_cost_mxn' => round($costMxn, 4),
                    'replacement_cost_mxn' => round($costMxn, 4),
                    'price_floor_mxn' => round($costMxn * 1.10, 2), 'calculated_at' => $now,
                ];
            }
            // Chunk-level transactions bound memory and preserve consistency for each slice.
            DB::transaction(static function () use ($products, $pivot, $supplied, $balances, $lots, $prices, $snapshots): void {
                DB::table('products')->insert($products);
                DB::table('product_categories')->insert($pivot);
                DB::table('supplier_products')->insert($supplied);
                DB::table('inventory_balances')->insert($balances);
                if ($lots !== []) DB::table('inventory_lots')->insert($lots);
                DB::table('product_pricing')->insert($prices);
                DB::table('product_price_snapshots')->insert($snapshots);
            });
            if ($end % 50000 === 0 || $end === $total) $this->command?->info("Synthetic products loaded: $end / $total");
        }
    }
}
