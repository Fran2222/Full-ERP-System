<?php

namespace App\Models\Service;

use App\Models\Branch;
use App\Models\Sales\Customer;
use App\Models\User;
use App\Models\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Model;

class ServiceJobOrder extends Model
{
    protected $fillable = [
        'job_order_no',
        'customer_id',
        'service_type_id',
        'service_status_id',
        'branch_id',
        'assigned_to_user_id',
        'vehicle_id',
        'created_by',
        'updated_by',
        'subject',
        'priority',
        'requested_date',
        'scheduled_at',
        'started_at',
        'completed_at',
        'site_address',
        'concern',
        'remarks',
        'status_text',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function serviceStatus()
    {
        return $this->belongsTo(ServiceStatus::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function reports()
    {
        return $this->hasMany(ServiceJobOrderReport::class);
    }

    public function getDisplayStatusAttribute()
    {
        return optional($this->serviceStatus)->name ?: ($this->status_text ?: 'Pending');
    }

    public function getStatusBadgeClassAttribute()
    {
        $name = strtolower($this->display_status);

        if (str_contains($name, 'complete')) {
            return 'bg-success';
        }

        if (str_contains($name, 'ongoing')) {
            return 'bg-primary';
        }

        if (str_contains($name, 'schedule')) {
            return 'bg-info';
        }

        if (str_contains($name, 'cancel')) {
            return 'bg-danger';
        }

        return 'bg-secondary';
    }
}
