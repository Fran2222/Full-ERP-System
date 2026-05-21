<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingCollection extends Model
{
    protected $table = 'accounting_collections';

    protected $fillable = [
        'collection_no',
        'collection_date',
        'accounting_bank_account_id',
        'credit_account_id',
        'accounting_journal_entry_id',
        'payer',
        'reference_no',
        'amount',
        'description',
        'status',
        'created_by',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'collection_date' => 'date',
        'amount' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(AccountingBankAccount::class, 'accounting_bank_account_id');
    }

    public function creditAccount(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'credit_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(AccountingJournalEntry::class, 'accounting_journal_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }
}
