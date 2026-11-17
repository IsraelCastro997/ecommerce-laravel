<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPolicyVersion extends Model
{
    protected function casts(): array
    {
        return [
            'configuration' => 'array',
            'approved_at' => 'datetime',
        ];
    }
}
