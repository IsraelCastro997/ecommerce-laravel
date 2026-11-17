<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPriceSnapshot extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'price_mxn' => 'decimal:2',
            'price_usd' => 'decimal:2',
            'historical_cost_mxn' => 'decimal:4',
            'replacement_cost_mxn' => 'decimal:4',
            'price_floor_mxn' => 'decimal:2',
            'calculated_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function pricePublication(): BelongsTo
    {
        return $this->belongsTo(PricePublication::class);
    }
}
