<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'folio',
        'supplier_id',
        'created_by',
        'currency',
        'exchange_rate',
        'subtotal',
        'total',
        'status',
        'ordered_at',
        'expected_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:4',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'ordered_at' => 'date',
            'expected_at' => 'date',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
