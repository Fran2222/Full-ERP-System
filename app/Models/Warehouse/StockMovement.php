<?php

namespace App\Models\Warehouse;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $table = 'warehouse_stock_movements';

    protected $fillable = [
        'item_id',
        'location_id',
        'movement_type',
        'quantity',
        'balance_after',
        'reference_type',
        'reference_id',
        'remarks',
        'transaction_date',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(WarehouseItem::class, 'item_id');
    }

    public function location()
    {
        return $this->belongsTo(WarehouseLocation::class, 'location_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDisplayReferenceAttribute()
    {
        return $this->reference_type ?: '-';
    }

    public function getDisplayTypeAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->movement_type));
    }
}