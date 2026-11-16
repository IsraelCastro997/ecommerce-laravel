<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $t) {
            $t->char('code', 3)->primary(); $t->string('name', 70); $t->unsignedTinyInteger('minor_units')->default(2);
        });
        Schema::create('exchange_rates', function (Blueprint $t) {
            $t->id(); $t->char('base_currency', 3); $t->char('quote_currency', 3);
            $t->decimal('rate', 18, 8); $t->string('source', 32); $t->string('series_id', 40);
            $t->date('effective_date'); $t->dateTime('published_at')->nullable(); $t->dateTime('fetched_at');
            $t->timestamps(); $t->unique(['source', 'series_id', 'effective_date'], 'fx_source_series_date_unique');
            $t->index(['base_currency', 'quote_currency', 'effective_date'], 'fx_pair_date_idx');
        });
        Schema::create('exchange_rate_sync_logs', function (Blueprint $t) {
            $t->id(); $t->string('source', 30); $t->string('status', 30);
            $t->text('error_message')->nullable(); $t->dateTime('started_at'); $t->dateTime('finished_at')->nullable();
        });
        Schema::create('markets', function (Blueprint $t) {
            $t->id(); $t->string('code', 24)->unique(); $t->char('primary_currency', 3); $t->string('name', 100);
        });
        Schema::create('pricing_policy_versions', function (Blueprint $t) {
            $t->id(); $t->string('name', 100); $t->json('configuration')->nullable();
            $t->timestamp('approved_at')->nullable(); $t->timestamps();
        });
        Schema::create('product_pricing', function (Blueprint $t) {
            $t->id(); $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('market_id')->constrained()->restrictOnDelete();
            $t->char('base_currency', 3); $t->decimal('base_price', 18, 4);
            $t->string('pricing_policy', 32)->default('protected_dynamic');
            $t->decimal('markup_percent', 8, 4)->default(25);
            $t->decimal('min_margin_percent', 8, 4)->default(10);
            $t->decimal('fx_buffer_percent', 8, 4)->default(0);
            $t->decimal('manual_floor_mxn', 18, 4)->nullable();
            $t->string('rounding_rule', 30)->default('cent'); $t->boolean('enabled')->default(true);
            $t->timestamps(); $t->unique(['product_id', 'market_id']);
        });
        Schema::create('price_publications', function (Blueprint $t) {
            $t->id(); $t->foreignId('market_id')->constrained()->restrictOnDelete();
            $t->foreignId('exchange_rate_id')->nullable()->constrained('exchange_rates')->restrictOnDelete();
            $t->string('status', 20)->default('building'); $t->boolean('is_active')->default(false);
            $t->unsignedBigInteger('expected_products')->default(0); $t->unsignedBigInteger('processed_products')->default(0);
            $t->timestamp('published_at')->nullable(); $t->timestamps();
            $t->index(['market_id', 'is_active']);
        });
        Schema::create('product_price_snapshots', function (Blueprint $t) {
            $t->id(); $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('price_publication_id')->constrained('price_publications')->cascadeOnDelete();
            $t->decimal('price_mxn', 18, 2); $t->decimal('price_usd', 18, 2);
            $t->decimal('historical_cost_mxn', 18, 4)->nullable();
            $t->decimal('replacement_cost_mxn', 18, 4)->nullable();
            $t->decimal('price_floor_mxn', 18, 2)->nullable();
            $t->timestamp('calculated_at');
            $t->unique(['product_id', 'price_publication_id'], 'price_snapshot_unique');
            $t->index(['price_publication_id', 'price_mxn', 'product_id'], 'price_filter_mxn_idx');
            $t->index(['price_publication_id', 'price_usd', 'product_id'], 'price_filter_usd_idx');
        });
        Schema::create('price_history', function (Blueprint $t) {
            $t->id(); $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->char('currency', 3); $t->decimal('old_amount', 18, 2)->nullable();
            $t->decimal('new_amount', 18, 2); $t->string('reason', 60);
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete(); $t->timestamps();
            $t->index(['product_id', 'created_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('price_history'); Schema::dropIfExists('product_price_snapshots');
        Schema::dropIfExists('price_publications'); Schema::dropIfExists('product_pricing');
        Schema::dropIfExists('pricing_policy_versions'); Schema::dropIfExists('markets');
        Schema::dropIfExists('exchange_rate_sync_logs'); Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
    }
};
