<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UssdCard extends Model
{
    protected $fillable = [
        'name',
        'number',
        'identifier',
        'is_valid',
        'validated',
        'service',
        'code',
        'amount',
        'quantity',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'validated' => 'boolean',
        'amount' => 'decimal:2',
        'quantity' => 'integer',
    ];
}
