<?php

namespace App\Models\Purchasing;

use App\Models\AccountingBankAccount;
use App\Models\AccountingJournalEntry;
use App\Models\User;
use App\Models\Warehouse\WarehouseSupplier as Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchasePayment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'payment_no',
        'purchase_order_id',
        'purchase_bill_id',
        'supplier_id',
        'accounting_bank_account_id',
        'accounting_journal_entry_id',
        'payment_date',
        'reference_no',
        'amount',
        'description',
        'status',
        'created_by',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function purchaseBill()
    {
        return $this->belongsTo(PurchaseBill::class, 'purchase_bill_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(AccountingBankAccount::class, 'accounting_bank_account_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(AccountingJournalEntry::class, 'accounting_journal_entry_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voider()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'posted' => 'Posted',
            'voided' => 'Voided',
            default => ucfirst((string) $this->status),
        };
    }

    public function scopeSearch($query, ?string $search)
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function ($subQuery) use ($search) {
            $subQuery->where('payment_no', 'ilike', "%{$search}%")
                ->orWhere('reference_no', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%")
                ->orWhere('status', 'ilike', "%{$search}%")
                ->orWhereHas('purchaseBill', function ($billQuery) use ($search) {
                    $billQuery->where('bill_no', 'ilike', "%{$search}%")
                        ->orWhere('reference_no', 'ilike', "%{$search}%");
                })
                ->orWhereHas('purchaseOrder', function ($poQuery) use ($search) {
                    $poQuery->where('po_no', 'ilike', "%{$search}%")
                        ->orWhere('reference_no', 'ilike', "%{$search}%");
                })
                ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                    $supplierQuery->where('supplier_name', 'ilike', "%{$search}%")
                        ->orWhere('contact_person', 'ilike', "%{$search}%");
                })
                ->orWhereHas('bankAccount', function ($bankQuery) use ($search) {
                    $bankQuery->where('name', 'ilike', "%{$search}%")
                        ->orWhere('bank_name', 'ilike', "%{$search}%")
                        ->orWhere('account_number', 'ilike', "%{$search}%");
                });
        });
    }
}