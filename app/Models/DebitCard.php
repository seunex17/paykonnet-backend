<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebitCard extends Model
{
    protected $fillable = [
        'user_id',
        'uuid',
        'card_number',
        'issuer',
        'country',
        'type',
        'expire',
        'token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
