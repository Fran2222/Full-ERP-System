<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseSupplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(WarehouseItem::class, 'supplier_id');
    }

    public function defaultItems()
    {
        return $this->hasMany(WarehouseItem::class, 'default_supplier_id');
    }

    public function getDisplayNameAttribute()
    {
        return $this->supplier_name;
    }
}