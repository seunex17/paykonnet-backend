<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CableSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'references',
        'provider_id',
        'amount',
        'phone_no',
        'smart_card_no',
        'details',
        'product',
        'package',
    ];
}
