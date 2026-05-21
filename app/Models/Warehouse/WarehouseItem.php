<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'item_code',
        'name',
        'item_name',
        'description',
        'image_path',
        'category_id',
        'unit_id',
        'supplier_id',
        'default_supplier_id',
        'cost_price',
        'selling_price',
        'reorder_level',
        'minimum_stock',
        'is_serialized',
        'is_service_unit',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'reorder_level' => 'integer',
        'minimum_stock' => 'integer',
        'is_serialized' => 'boolean',
        'is_service_unit' => 'boolean',
        'status' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(WarehouseCategory::class, 'category_id');
    }

    public function unit()
    {
        return $this->belongsTo(WarehouseUnit::class, 'unit_id');
    }

    public function supplier()
    {
        return $this->belongsTo(WarehouseSupplier::class, 'supplier_id');
    }

    public function defaultSupplier()
    {
        return $this->belongsTo(WarehouseSupplier::class, 'default_supplier_id');
    }

    public function serials()
    {
        return $this->hasMany(WarehouseItemSerial::class, 'item_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'item_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'item_id');
    }

    public function serviceUnitBorrows()
    {
        return $this->hasMany(WarehouseServiceUnitBorrow::class, 'item_id');
    }

    public function getDisplayCodeAttribute()
    {
        return $this->code ?: $this->item_code;
    }

    public function getDisplayNameAttribute()
    {
        return $this->name ?: $this->item_name;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}
