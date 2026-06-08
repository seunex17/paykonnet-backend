<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataBank extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'references',
        'provider_id',
        'plan',
        'plan_code',
        'data_value',
        'is_valid',
        'provider_image',
        'price',
    ];
}
