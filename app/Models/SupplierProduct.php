<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProduct extends Model
{
    protected function casts(): array
    {
        return [
            'quoted_unit_cost' => 'decimal:4',
            'lead_time_days' => 'integer',
            'is_preferred' => 'boolean',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseCurrencyDefinition(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'purchase_currency', 'code');
    }
}
