<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashLoan extends Model
{
    protected $fillable = [
        'user_id',
        'uuid',
        'fully_paid',
        'nin',
        'guarantor_email',
        'guarantor_phone_number',
        'device_id',
        'debit_card_id',
        'amount',
        'repayment_amount',
        'amount_paid',
        'due_date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function debitCard(): BelongsTo
    {
        return $this->belongsTo(DebitCard::class);
    }
}
