<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'branch_id',
        'department_id',
        'project_id',
        'project_site_text',
        'purpose',
        'start_date',
        'end_date',
        'status',
        'remarks',
        'assigned_by',
        'ended_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function endedBy()
    {
        return $this->belongsTo(User::class, 'ended_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function members()
    {
        return $this->hasMany(VehicleAssignmentMember::class);
    }

    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', (string) $this->status));
    }

    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'active' => 'badge bg-success',
            'ended' => 'badge bg-secondary',
            'cancelled' => 'badge bg-danger',
            default => 'badge bg-light text-dark',
        };
    }
}
