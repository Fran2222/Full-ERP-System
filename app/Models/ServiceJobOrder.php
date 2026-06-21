<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceJobOrder extends Model
{
    protected $table = 'service_job_orders';

    protected $guarded = [];

    protected $casts = [
        'requested_date' => 'date',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function serviceStatus()
    {
        return $this->belongsTo(ServiceStatus::class, 'service_status_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}