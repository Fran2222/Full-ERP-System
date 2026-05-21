<?php

namespace App\Models\Sales;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_no',
        'customer_id',
        'branch_id',
        'location_id',
        'receipt_date',
        'payment_method',
        'reference_no',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'status',
        'notes',
        'created_by',
        'accounting_bank_account_id',
        'accounting_journal_entry_id',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SalesReceiptItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\Warehouse\WarehouseLocation::class);
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