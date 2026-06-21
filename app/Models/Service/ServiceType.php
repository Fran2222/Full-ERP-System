<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    protected $fillable = ['name', 'description', 'status'];
}
