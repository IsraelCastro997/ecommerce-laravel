<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPricing extends Model
{
    protected $table = 'product_pricing';

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:4',
            'markup_percent' => 'decimal:4',
            'min_margin_percent' => 'decimal:4',
            'fx_buffer_percent' => 'decimal:4',
            'manual_floor_mxn' => 'decimal:4',
            'enabled' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function baseCurrencyDefinition(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency', 'code');
    }
}
