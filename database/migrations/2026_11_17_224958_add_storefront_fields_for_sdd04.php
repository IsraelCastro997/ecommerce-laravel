<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('products', function(Blueprint $t) { $t->timestamp('available_from')->nullable(); $t->timestamp('available_until')->nullable(); $t->index(['status','available_until','id'], 'storefront_active_idx'); });
  Schema::table('orders', function(Blueprint $t) { $t->string('customer_email')->nullable(); });
 }
 public function down(): void {
  Schema::table('orders', fn(Blueprint $t) => $t->dropColumn('customer_email'));
  Schema::table('products', function(Blueprint $t) { $t->dropIndex('storefront_active_idx'); $t->dropColumn(['available_from','available_until']); });
 }
};
