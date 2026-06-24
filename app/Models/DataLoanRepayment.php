<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataLoanRepayment extends Model
{
    protected $fillable = [
        'user_id',
        'data_loan_id',
        'amount',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dataLoan(): BelongsTo
    {
        return $this->belongsTo(DataLoan::class);
    }
}
