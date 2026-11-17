<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Access attribute values through the product/attribute relation so Eloquent retains both pivot keys.
 */
class ProductAttributeValue extends Pivot
{
    protected $table = 'product_attribute_values';

    public $timestamps = false;

    protected $fillable = ['value'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }
}
