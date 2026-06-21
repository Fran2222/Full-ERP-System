<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceStatus extends Model
{
    protected $table = 'service_statuses';

    protected $guarded = [];

    protected $casts = [
        'is_closed' => 'boolean',
        'status' => 'boolean',
    ];
}