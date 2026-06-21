<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleAssignmentMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_assignment_id',
        'user_id',
        'employee_id',
        'role_in_vehicle',
    ];

    public function assignment()
    {
        return $this->belongsTo(VehicleAssignment::class, 'vehicle_assignment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
