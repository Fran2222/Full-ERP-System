<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Model;

class ServiceStatus extends Model
{
    protected $fillable = ['name', 'color', 'sort_order', 'is_closed', 'status'];

    protected $casts = [
        'is_closed' => 'boolean',
    ];
}
