<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlexDataList extends Model
{
    protected $fillable = [
        'status',
        'service_id',
        'name',
        'code',
        'price',
        'duration',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }
}
