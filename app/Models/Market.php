<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Market extends Model
{
    public $timestamps = false;

    protected $fillable = ['code', 'name'];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'primary_currency', 'code');
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(ProductPricing::class);
    }

    public function pricePublications(): HasMany
    {
        return $this->hasMany(PricePublication::class);
    }
}
