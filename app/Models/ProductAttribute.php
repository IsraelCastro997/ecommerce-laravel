<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductAttribute extends Model
{
    protected $fillable = [
        'code',
        'label',
        'data_type',
        'filterable',
    ];

    protected function casts(): array
    {
        return [
            'filterable' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attribute_values')
            ->using(ProductAttributeValue::class)
            ->withPivot('value');
    }
}
