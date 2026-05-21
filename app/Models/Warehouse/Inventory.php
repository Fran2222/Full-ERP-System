<?php

namespace App\Models\Warehouse;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'warehouse_inventories';

    protected $fillable = ['item_id','branch_id','location_id','quantity'];

    public function item(){ return $this->belongsTo(WarehouseItem::class, 'item_id'); }
    public function branch(){ return $this->belongsTo(Branch::class, 'branch_id'); }
    public function location(){ return $this->belongsTo(WarehouseLocation::class, 'location_id'); }
}
