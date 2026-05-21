<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviation',
    ];

    public function items()
    {
        return $this->hasMany(WarehouseItem::class, 'unit_id');
    }

    public function getDisplayNameAttribute()
    {
        return $this->name;
    }

    public function getDisplayAbbreviationAttribute()
    {
        return $this->abbreviation;
    }
}