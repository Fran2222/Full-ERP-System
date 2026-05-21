<?php

namespace App\Models\Sales;

use App\Models\Warehouse\WarehouseItem;
use App\Models\Warehouse\WarehouseItemSerial;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_receipt_id',
        'item_id',
        'item_code',
        'item_name',
        'description',
        'quantity',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function salesReceipt()
    {
        return $this->belongsTo(SalesReceipt::class);
    }

    public function warehouseItem()
    {
        return $this->belongsTo(WarehouseItem::class, 'item_id');
    }

    public function serials()
    {
        return $this->belongsToMany(
            WarehouseItemSerial::class,
            'sales_receipt_item_serials',
            'sales_receipt_item_id',
            'warehouse_item_serial_id'
        )->withTimestamps();
    }
}