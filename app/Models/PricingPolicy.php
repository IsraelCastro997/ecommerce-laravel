<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingPolicy extends Model
{
    //
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
        'strategy',
        'markup_percent',
        'min_margin_percent',
        'fx_buffer_percent',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'markup_percent' => 'decimal:4',
        'min_margin_percent' => 'decimal:4',
        'fx_buffer_percent' => 'decimal:4',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
