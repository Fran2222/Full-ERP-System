<?php

namespace App\Models\Warehouse;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_code',
        'location_name',
        'location_type',
        'branch_id',
        'address',
        'status',
        'name',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getDisplayNameAttribute()
    {
        return $this->location_name ?: $this->name;
    }

    public function getDisplayCodeAttribute()
    {
        return $this->location_code;
    }
}