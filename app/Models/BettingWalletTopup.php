<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BettingWalletTopup extends Model
{
    protected $fillable = [
        'user_id',
        'references',
        'company',
        'amount',
        'customer_id',
    ];
}
