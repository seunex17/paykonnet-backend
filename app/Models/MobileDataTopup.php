<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileDataTopup extends Model
{
    protected $fillable = [
        'user_id',
        'references',
        'provider_id',
        'amount',
        'phone_no',
        'details',
        'plan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
