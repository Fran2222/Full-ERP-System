<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'color',
        'sort_order',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'status_id');
    }
}
