<?php

namespace App\Models\ProjectMgmt;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectStatus extends Model
{
    use HasFactory;

    protected $table = 'project_statuses';

    protected $fillable = [
        'code',
        'name',
        'description',
        'sort_order',
        'status',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'status_id');
    }
}