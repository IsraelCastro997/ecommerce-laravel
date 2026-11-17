<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('folio',50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete()->nullable();
            $table->string('currency',3)->default('MXN');
            $table->decimal('exchange_rate', 10, 4)->default(1)->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('status', 30)->default('draft');
            $table->date('ordered_at')->nullable();
            $table->date('expected_at')->nullable();
            $table->text('notes')->nullable();

            $table->index('ordered_at');
            $table->index(['status', 'supplier_id']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
