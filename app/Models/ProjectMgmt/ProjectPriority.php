<?php

namespace App\Models\ProjectMgmt;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPriority extends Model
{
    use HasFactory;

    protected $table = 'project_priorities';

    protected $fillable = [
        'code',
        'name',
        'description',
        'level',
        'status',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'priority_id');
    }
}