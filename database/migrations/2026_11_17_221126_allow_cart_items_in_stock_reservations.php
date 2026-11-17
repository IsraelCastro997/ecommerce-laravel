<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('order_item_id')->nullable()->change();
            $table->foreignId('cart_item_id')->nullable()->constrained('cart_items')->restrictOnDelete();
            $table->index(['cart_item_id', 'status', 'expires_at'], 'reservation_cart_status_expiry_idx');
        });
    }

    public function down(): void
    {
        if (DB::table('stock_reservations')->whereNotNull('cart_item_id')->orWhereNull('order_item_id')->exists()) {
            throw new RuntimeException('Migrate or remove cart reservations explicitly before reverting their schema.');
        }

        Schema::table('stock_reservations', function (Blueprint $table) {
            $table->dropForeign(['cart_item_id']);
            $table->dropIndex('reservation_cart_status_expiry_idx');
            $table->dropColumn('cart_item_id');
            $table->unsignedBigInteger('order_item_id')->nullable(false)->change();
        });
    }
};
