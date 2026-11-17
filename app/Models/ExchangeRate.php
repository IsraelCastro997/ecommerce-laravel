<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExchangeRate extends Model
{
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:8',
            'effective_date' => 'date:Y-m-d',
            'published_at' => 'datetime',
            'fetched_at' => 'datetime',
        ];
    }

    public function baseCurrencyDefinition(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency', 'code');
    }

    public function quoteCurrencyDefinition(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'quote_currency', 'code');
    }

    public function pricePublications(): HasMany
    {
        return $this->hasMany(PricePublication::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
