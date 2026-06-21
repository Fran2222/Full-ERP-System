<?php

namespace App\Models\ProjectMgmt;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectVehicle extends Model
{
    use HasFactory;

    protected $table = 'project_vehicles';

    protected $fillable = [
        'sequence_no', 'vehicle_code', 'plate_name', 'description', 'status', 'created_by', 'updated_by',
    ];

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_vehicle_drivers', 'project_vehicle_id', 'user_id')->withTimestamps();
    }

    public function gasSlips(): HasMany
    {
        return $this->hasMany(ProjectGasSlip::class, 'project_vehicle_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
