<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $t) {
            $t->id(); $t->string('name', 120); $t->string('slug', 150)->unique(); $t->timestamps();
        });
        Schema::create('categories', function (Blueprint $t) {
            $t->id(); $t->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $t->string('name', 150); $t->string('slug', 180)->unique(); $t->boolean('active')->default(true);
            $t->index(['parent_id', 'active']); $t->timestamps();
        });
        $supportsFullText = in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true);
        Schema::create('products', function (Blueprint $t) use ($supportsFullText) {
            $t->id(); $t->string('sku', 64)->unique(); $t->string('slug', 220)->unique(); $t->string('name', 255);
            $t->text('description')->nullable(); $t->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $t->foreignId('primary_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $t->char('origin_country', 2)->nullable(); $t->string('status', 20)->default('draft');
            $t->unsignedBigInteger('version')->default(1); $t->timestamps(); $t->softDeletes();
            $t->index(['status', 'id']); $t->index(['brand_id', 'status', 'id']);
            $t->index(['primary_category_id', 'status', 'id']);
            if ($supportsFullText) {
                $t->fullText(['name', 'description']);
            }
        });
        Schema::create('product_categories', function (Blueprint $t) {
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('category_id')->constrained()->cascadeOnDelete();
            $t->primary(['product_id', 'category_id']); $t->index(['category_id', 'product_id']);
        });
        Schema::create('product_attributes', function (Blueprint $t) {
            $t->id(); $t->string('code', 70)->unique(); $t->string('label', 130);
            $t->string('data_type', 20)->default('text'); $t->boolean('filterable')->default(false); $t->timestamps();
        });
        Schema::create('product_attribute_values', function (Blueprint $t) {
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $t->string('value', 180); $t->primary(['product_id', 'product_attribute_id']);
            $t->index(['product_attribute_id', 'value', 'product_id'], 'attribute_value_filter_idx');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values'); Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('product_categories'); Schema::dropIfExists('products');
        Schema::dropIfExists('categories'); Schema::dropIfExists('brands');
    }
};
