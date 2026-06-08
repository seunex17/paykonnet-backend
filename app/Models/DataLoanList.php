<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataLoanList extends Model
{
    protected $fillable = [
        'service_id',
        'name',
        'code',
        'price',
    ];
}
