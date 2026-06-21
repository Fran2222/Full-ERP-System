<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_code',
        'plate_number',
        'vehicle_type_id',
        'status_id',
        'assigned_branch_id',
        'default_driver_id',
        'brand',
        'model',
        'year_model',
        'color',
        'fuel_type',
        'engine_no',
        'chassis_no',
        'current_odometer',
        'acquisition_date',
        'acquisition_cost',
        'photo_path',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'current_odometer' => 'integer',
    ];

    protected $appends = [
        'display_name',
        'photo_url',
    ];

    public function getDisplayNameAttribute(): string
    {
        $parts = array_filter([
            $this->vehicle_code,
            $this->plate_number,
            trim(($this->brand ?? '') . ' ' . ($this->model ?? '')),
        ]);

        return implode(' - ', $parts) ?: 'Vehicle #' . $this->id;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }

    public function type()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function status()
    {
        return $this->belongsTo(VehicleStatus::class, 'status_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'assigned_branch_id');
    }

    public function defaultDriver()
    {
        return $this->belongsTo(User::class, 'default_driver_id');
    }

    public function assignments()
    {
        return $this->hasMany(VehicleAssignment::class);
    }

    public function activeAssignment()
    {
        return $this->hasOne(VehicleAssignment::class)->where('status', 'active')->latestOfMany();
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(VehicleMaintenanceRecord::class);
    }

    public function documents()
    {
        return $this->hasMany(VehicleDocument::class);
    }
}
