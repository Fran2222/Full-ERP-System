<?php

namespace App\Models\Warehouse;

use App\Models\Branch;
use App\Models\Sales\SalesReceiptItem;
use Illuminate\Database\Eloquent\Model;

class WarehouseItemSerial extends Model
{
    protected $table = 'warehouse_item_serials';

    protected $fillable = [
        'item_id',
        'branch_id',
        'location_id',
        'serial_number',
        'status',
        'stock_in_movement_id',
        'stock_out_movement_id',
        'issued_at',
        'remarks',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(WarehouseItem::class, 'item_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function location()
    {
        return $this->belongsTo(WarehouseLocation::class, 'location_id');
    }

    public function stockInMovement()
    {
        return $this->belongsTo(StockMovement::class, 'stock_in_movement_id');
    }

    public function stockOutMovement()
    {
        return $this->belongsTo(StockMovement::class, 'stock_out_movement_id');
    }

    public function serviceUnitBorrows()
    {
        return $this->hasMany(WarehouseServiceUnitBorrow::class, 'serial_id');
    }

    public function activeServiceUnitBorrow()
    {
        return $this->hasOne(WarehouseServiceUnitBorrow::class, 'serial_id')->where('status', 'active');
    }

    public function salesReceiptItems()
    {
        return $this->belongsToMany(
            SalesReceiptItem::class,
            'sales_receipt_item_serials',
            'warehouse_item_serial_id',
            'sales_receipt_item_id'
        )->withTimestamps();
    }
}