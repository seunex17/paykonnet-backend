<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectricityBill extends Model
{
    protected $fillable = [
        'user_id',
        'references',
        'provider_id',
        'amount',
        'phone_no',
        'meter_no',
        'details',
        'token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
