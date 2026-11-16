<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->uuid('guest_token')->nullable()->unique(); $t->char('currency', 3)->default('MXN');
            $t->timestamp('expires_at')->nullable(); $t->timestamps(); $t->index(['user_id', 'updated_at']);
        });
        Schema::create('cart_items', function (Blueprint $t) {
            $t->id(); $t->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->unsignedInteger('quantity'); $t->timestamps(); $t->unique(['cart_id', 'product_id']);
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id(); $t->string('order_number', 36)->unique();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->uuid('idempotency_key')->unique(); $t->string('status', 30)->default('pending_payment');
            $t->char('currency', 3); $t->decimal('subtotal', 18, 2); $t->decimal('tax_total', 18, 2)->default(0);
            $t->decimal('discount_total', 18, 2)->default(0); $t->decimal('shipping_total', 18, 2)->default(0);
            $t->decimal('grand_total', 18, 2);
            $t->foreignId('exchange_rate_id')->nullable()->constrained('exchange_rates')->restrictOnDelete();
            $t->decimal('exchange_rate_value', 18, 8)->nullable();
            $t->foreignId('price_publication_id')->nullable()->constrained('price_publications')->restrictOnDelete();
            $t->json('shipping_address_snapshot')->nullable(); $t->timestamp('paid_at')->nullable();
            $t->timestamps(); $t->index(['user_id', 'created_at', 'id']); $t->index(['status', 'created_at']);
        });
        Schema::create('order_items', function (Blueprint $t) {
            $t->id(); $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->string('sku_snapshot', 64); $t->string('name_snapshot', 255);
            $t->unsignedInteger('quantity'); $t->decimal('unit_price', 18, 2);
            $t->decimal('tax_total', 18, 2)->default(0); $t->decimal('discount_total', 18, 2)->default(0);
            $t->decimal('line_total', 18, 2); $t->decimal('allocated_cost_mxn', 18, 4)->nullable();
            $t->timestamps(); $t->index(['order_id', 'product_id']);
        });
        Schema::create('stock_reservations', function (Blueprint $t) {
            $t->id(); $t->uuid('operation_id')->unique(); $t->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $t->unsignedInteger('quantity'); $t->string('status', 24)->default('held');
            $t->timestamp('expires_at'); $t->timestamps(); $t->index(['status', 'expires_at']);
        });
        Schema::create('stock_allocations', function (Blueprint $t) {
            $t->id(); $t->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $t->foreignId('inventory_lot_id')->constrained('inventory_lots')->restrictOnDelete();
            $t->unsignedInteger('quantity'); $t->decimal('unit_cost_mxn', 18, 4);
            $t->timestamps(); $t->unique(['order_item_id', 'inventory_lot_id']);
        });
        Schema::create('payment_transactions', function (Blueprint $t) {
            $t->id(); $t->foreignId('order_id')->constrained()->restrictOnDelete();
            $t->string('provider', 25)->default('stripe'); $t->string('status', 30);
            $t->string('stripe_checkout_session_id', 255)->nullable()->unique();
            $t->string('stripe_payment_intent_id', 255)->nullable()->unique();
            $t->char('currency', 3); $t->decimal('amount', 18, 2);
            $t->timestamps(); $t->index(['order_id', 'status']);
        });
        Schema::create('stripe_events', function (Blueprint $t) {
            $t->id(); $t->string('stripe_event_id', 255)->unique(); $t->string('event_type', 120);
            $t->char('payload_sha256', 64); $t->string('status', 24)->default('received');
            $t->timestamp('processed_at')->nullable(); $t->text('last_error')->nullable(); $t->timestamps();
            $t->index(['status', 'created_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('stripe_events'); Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('stock_allocations'); Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('order_items'); Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items'); Schema::dropIfExists('carts');
    }
};
