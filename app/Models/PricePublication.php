<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricePublication extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'expected_products' => 'integer',
            'processed_products' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function exchangeRate(): BelongsTo
    {
        return $this->belongsTo(ExchangeRate::class);
    }

    public function priceSnapshots(): HasMany
    {
        return $this->hasMany(ProductPriceSnapshot::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
