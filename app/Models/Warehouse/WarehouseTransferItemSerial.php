<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\Model;

class WarehouseTransferItemSerial extends Model
{
    protected $table = 'warehouse_transfer_item_serials';

    protected $fillable = [
        'transfer_item_id',
        'warehouse_item_serial_id',
    ];
}
