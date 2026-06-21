<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceJobOrderReport extends Model
{
    protected $table = 'service_job_order_reports';

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function jobOrder()
    {
        return $this->belongsTo(ServiceJobOrder::class, 'service_job_order_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    public function statusUpdate()
    {
        return $this->belongsTo(ServiceStatus::class, 'status_update_id');
    }

    public function photos()
    {
        return $this->hasMany(ServiceJobOrderReportPhoto::class, 'service_job_order_report_id');
    }
}