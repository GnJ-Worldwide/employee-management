<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerTransaction extends Model
{
    protected $fillable = [
        'employee_id',
        'contract_id',
        'transaction_date',
        'type',
        'category',
        'expense_type',
        'amount',
        'balance',
        'vendor_name',
        'bill_number',
        'bill_image',
        'reference_no',
        'description',
        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($transaction) {

            /*
            |--------------------------------------------------------------------------
            | Running Balance Logic
            |--------------------------------------------------------------------------
            |
            | Debit  = Money Given / Expense
            | Credit = Money Received / Settlement
            |
            */

            $lastBalance = self::where(
                'employee_id',
                $transaction->employee_id
            )
            ->latest('id')
            ->value('balance') ?? 0;

            if ($transaction->type === 'debit') {

                $transaction->balance =
                    $lastBalance + $transaction->amount;

            } else {

                $transaction->balance =
                    $lastBalance - $transaction->amount;
            }

            /*
            |--------------------------------------------------------------------------
            | Auto Created By
            |--------------------------------------------------------------------------
            */

            if (auth()->check()) {
                $transaction->created_by = auth()->id();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->amount, 2);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return '₹' . number_format($this->balance, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isDebit(): bool
    {
        return $this->type === 'debit';
    }

    public function isCredit(): bool
    {
        return $this->type === 'credit';
    }
}