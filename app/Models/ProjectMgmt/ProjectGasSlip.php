<?php

namespace App\Models\ProjectMgmt;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProjectGasSlip extends Model
{
    use HasFactory;

    protected $table = 'project_gas_slips';

    protected $fillable = [
        'po_no', 'project_vehicle_id', 'location', 'amount', 'issued_date', 'returned_date', 'attachment_path',
        'attachment_original_name', 'attachment_mime_type', 'attachment_size', 'status', 'remarks', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issued_date' => 'date',
        'returned_date' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(ProjectVehicle::class, 'project_vehicle_id');
    }

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_gas_slip_drivers', 'project_gas_slip_id', 'user_id')->withTimestamps();
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
