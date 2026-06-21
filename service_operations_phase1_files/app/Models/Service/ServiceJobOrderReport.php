<?php

namespace App\Models\Service;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ServiceJobOrderReport extends Model
{
    protected $fillable = [
        'service_job_order_id',
        'reported_by',
        'reported_at',
        'findings',
        'work_done',
        'recommendation',
        'customer_acknowledged_by',
        'status_after_report',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    public function jobOrder()
    {
        return $this->belongsTo(ServiceJobOrder::class, 'service_job_order_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
