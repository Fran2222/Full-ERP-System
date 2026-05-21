<?php

namespace App\Models\Purchasing;

use App\Models\AccountingJournalEntry;
use App\Models\User;
use App\Models\Warehouse\WarehouseSupplier as Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseBill extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'bill_no',
        'purchase_order_id',
        'supplier_id',
        'accounting_journal_entry_id',
        'bill_date',
        'due_date',
        'reference_no',
        'subtotal',
        'tax_amount',
        'total_amount',
        'description',
        'status',
        'created_by',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_bill_id');
    }

    public function postedPayments()
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_bill_id')
            ->where('status', 'posted');
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

    public function getPaidAmountAttribute(): float
    {
        if ($this->relationLoaded('postedPayments')) {
            return (float) $this->postedPayments->sum(fn ($payment) => (float) $payment->amount);
        }

        return (float) $this->postedPayments()->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return max(0, round((float) $this->total_amount - $this->paid_amount, 2));
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->status === 'voided') {
            return 'Voided';
        }

        $paid = round($this->paid_amount, 2);
        $total = round((float) $this->total_amount, 2);

        if ($total <= 0 || $paid <= 0) {
            return 'Unpaid';
        }

        if ($paid >= $total) {
            return 'Paid';
        }

        return 'Partially Paid';
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
            $subQuery->where('bill_no', 'ilike', "%{$search}%")
                ->orWhere('reference_no', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%")
                ->orWhere('status', 'ilike', "%{$search}%")
                ->orWhereHas('purchaseOrder', function ($poQuery) use ($search) {
                    $poQuery->where('po_no', 'ilike', "%{$search}%")
                        ->orWhere('reference_no', 'ilike', "%{$search}%");
                })
                ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                    $supplierQuery->where('supplier_name', 'ilike', "%{$search}%")
                        ->orWhere('contact_person', 'ilike', "%{$search}%");
                });
        });
    }
}
