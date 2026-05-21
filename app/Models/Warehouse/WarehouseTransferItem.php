<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\Model;

class WarehouseTransferItem extends Model
{
    protected $fillable = [
        'transfer_id',
        'item_id',
        'quantity',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function transfer()
    {
        return $this->belongsTo(WarehouseTransfer::class, 'transfer_id');
    }

    public function item()
    {
        return $this->belongsTo(WarehouseItem::class, 'item_id');
    }

    public function serials()
    {
        return $this->belongsToMany(
            WarehouseItemSerial::class,
            'warehouse_transfer_item_serials',
            'transfer_item_id',
            'warehouse_item_serial_id'
        )->withTimestamps();
    }
}
