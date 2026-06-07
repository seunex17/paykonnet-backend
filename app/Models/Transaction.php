<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'references',
        'amount',
        'status',
        'note',
        'source_table',
    ];

    /**
     * @var array<int, string>
     */
    protected $casts = [
        'user_id' => 'string',
        'type' => 'string',
        'references' => 'string',
        'amount' => 'string',
        'note' => 'string',
        'status' => 'string',
        'source_table' => 'string',
    ];
}
