<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleMaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'maintenance_type_id',
        'reported_by',
        'performed_by',
        'maintenance_date',
        'odometer',
        'issue_or_concern',
        'action_taken',
        'parts_replaced',
        'shop_or_mechanic',
        'labor_cost',
        'parts_cost',
        'other_cost',
        'total_cost',
        'next_maintenance_date',
        'next_maintenance_odometer',
        'status',
        'attachment_path',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'labor_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'other_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function maintenanceType()
    {
        return $this->belongsTo(VehicleMaintenanceType::class, 'maintenance_type_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', (string) $this->status));
    }

    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'open' => 'badge bg-warning',
            'in_progress' => 'badge bg-primary',
            'completed' => 'badge bg-success',
            'cancelled' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }
}
