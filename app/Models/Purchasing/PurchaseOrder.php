<?php

namespace App\Models\Purchasing;

use App\Models\User;
use App\Models\Warehouse\WarehouseLocation as Location;
use App\Models\Warehouse\WarehouseSupplier as Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_no',
        'supplier_id',
        'po_date',
        'expected_date',
        'location_id',
        'reference_no',
        'ship_via',
        'payment_terms',
        'subtotal',
        'tax_amount',
        'total_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'po_date' => 'date',
        'expected_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_order_id');
    }

    public function bills()
    {
        return $this->hasMany(PurchaseBill::class, 'purchase_order_id');
    }

    public function postedBills()
    {
        return $this->hasMany(PurchaseBill::class, 'purchase_order_id')
            ->where('status', 'posted');
    }

    public function postedPayments()
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_order_id')
            ->where('status', 'posted');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
