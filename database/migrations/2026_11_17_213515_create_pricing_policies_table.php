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
        Schema::create('pricing_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name',150);
            $table->text('description')->nullable();
            $table->decimal('code', 60)->unique();
            $table->enum('strategy', [
            'fixed_base',
            'cost_plus',
            'protected_dinamic',
            'manual']);
          
             $table->decimal('markup_percent', 7, 4)->default(0);
             $table->decimal('min_margin_percent', 7, 4)->default(0);
             $table->decimal('fx_buffer_percent', 7, 4)->default(0);
             $table->boolean('is_active')->default(true);
            
             $table->timestamps();
             $table->index(['is_active', 'strategy']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_policies');
    }
};
