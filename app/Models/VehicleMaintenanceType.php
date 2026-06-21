<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleMaintenanceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'status',
        'remarks',
    ];

    public function maintenanceRecords()
    {
        return $this->hasMany(VehicleMaintenanceRecord::class, 'maintenance_type_id');
    }
}
