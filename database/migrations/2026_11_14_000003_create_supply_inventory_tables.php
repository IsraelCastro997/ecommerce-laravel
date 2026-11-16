<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $t) {
            $t->id(); $t->string('name', 180); $t->char('country_code', 2)->nullable(); $t->timestamps();
        });
        Schema::create('supplier_products', function (Blueprint $t) {
            $t->id(); $t->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('supplier_sku', 80)->nullable(); $t->char('purchase_currency', 3);
            $t->decimal('quoted_unit_cost', 18, 4); $t->unsignedInteger('lead_time_days')->default(0);
            $t->boolean('is_preferred')->default(false); $t->timestamps();
            $t->unique(['supplier_id', 'product_id']); $t->index(['product_id', 'is_preferred']);
        });
        Schema::create('warehouses', function (Blueprint $t) {
            $t->id(); $t->string('code', 40)->unique(); $t->string('name', 150); $t->timestamps();
        });
        Schema::create('inventory_balances', function (Blueprint $t) {
            $t->id(); $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('qty_on_hand')->default(0); $t->unsignedInteger('qty_reserved')->default(0);
            $t->unsignedBigInteger('version')->default(1); $t->timestamps();
            $t->unique(['product_id', 'warehouse_id']); $t->index(['warehouse_id', 'product_id']);
        });
        Schema::create('inventory_lots', function (Blueprint $t) {
            $t->id(); $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $t->string('receipt_reference', 100)->nullable(); $t->unsignedInteger('quantity_received');
            $t->unsignedInteger('quantity_remaining'); $t->char('purchase_currency', 3);
            $t->decimal('unit_cost_original', 18, 4); $t->decimal('acquisition_fx_rate', 18, 8);
            $t->decimal('unit_landed_cost_mxn', 18, 4); $t->dateTime('received_at'); $t->timestamps();
            $t->index(['product_id', 'warehouse_id', 'quantity_remaining'], 'lot_stock_idx');
        });
        Schema::create('inventory_movements', function (Blueprint $t) {
            $t->id(); $t->uuid('operation_id')->unique(); $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $t->foreignId('inventory_lot_id')->nullable()->constrained('inventory_lots')->restrictOnDelete();
            $t->string('movement_type', 30); $t->integer('quantity_delta');
            $t->string('reference', 100)->nullable(); $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps(); $t->index(['product_id', 'created_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements'); Schema::dropIfExists('inventory_lots');
        Schema::dropIfExists('inventory_balances'); Schema::dropIfExists('warehouses');
        Schema::dropIfExists('supplier_products'); Schema::dropIfExists('suppliers');
    }
};
