<?php

namespace App\Models\Sales;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_no',
        'customer_id',
        'invoice_id',
        'payment_date',
        'payment_method',
        'reference_no',
        'amount',
        'notes',
        'created_by',
        'accounting_bank_account_id',
        'accounting_journal_entry_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accountingBankAccount()
    {
        return $this->belongsTo(\App\Models\AccountingBankAccount::class, 'accounting_bank_account_id');
    }

    public function accountingJournalEntry()
    {
        return $this->belongsTo(\App\Models\AccountingJournalEntry::class, 'accounting_journal_entry_id');
    }
}