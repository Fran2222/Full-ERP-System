<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmPipelineStage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'position',
        'color',
        'is_default',
        'is_locked',
        'status',
        'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function leads()
    {
        return $this->hasMany(CrmLead::class, 'stage_id');
    }

    public function activeLeads()
    {
        return $this->hasMany(CrmLead::class, 'stage_id')
            ->whereNull('deleted_at');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}