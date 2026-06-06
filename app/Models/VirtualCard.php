<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VirtualCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_active',
        'card_id',
        'account_id',
        'currency',
        'card_pan',
        'masked_pan',
        'city',
        'state',
        'address',
        'cvv',
        'expiration',
        'card_type',
        'name_on_card',
        'is_block',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_block' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
