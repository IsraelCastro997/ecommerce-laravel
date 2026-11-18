<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('products',function(Blueprint $t){$t->string('short_description',500)->nullable();$t->text('long_description')->nullable();});
  Schema::table('order_items',function(Blueprint $t){$t->decimal('price_usd',18,2)->nullable();$t->decimal('price_mxn',18,2)->nullable();});
 }
 public function down(): void {
  Schema::table('order_items',fn(Blueprint $t)=>$t->dropColumn(['price_usd','price_mxn']));
  Schema::table('products',fn(Blueprint $t)=>$t->dropColumn(['short_description','long_description']));
 }
};
