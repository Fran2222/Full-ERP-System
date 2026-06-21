<?php

namespace App\Models\Purchasing;

use App\Models\Warehouse\WarehouseItem as Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'item_code',
        'item_name',
        'description',
        'quantity',
        'received_quantity',
        'unit_name',
        'unit_price',
        'tax_amount',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function getRemainingQuantityAttribute(): float
    {
        return max(0, (float) $this->quantity - (float) $this->received_quantity);
    }

    public function getReceivingStatusAttribute(): string
    {
        if ((float) $this->received_quantity <= 0) {
            return 'pending';
        }

        if ((float) $this->remaining_quantity <= 0) {
            return 'received';
        }

        return 'partial';
    }
}
