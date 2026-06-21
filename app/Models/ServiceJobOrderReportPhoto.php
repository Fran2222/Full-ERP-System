<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceJobOrderReportPhoto extends Model
{
    protected $table = 'service_job_order_report_photos';

    protected $guarded = [];

    public function report()
    {
        return $this->belongsTo(ServiceJobOrderReport::class, 'service_job_order_report_id');
    }
}