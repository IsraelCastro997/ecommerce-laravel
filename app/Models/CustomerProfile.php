<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProfile extends Model
{
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = ['display_name', 'phone', 'preferred_currency'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
