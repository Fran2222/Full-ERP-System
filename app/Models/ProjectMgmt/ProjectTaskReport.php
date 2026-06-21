<?php

namespace App\Models\ProjectMgmt;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTaskReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_task_id',
        'reported_by',
        'progress_percent',
        'report_details',
        'photo_paths',
    ];

    protected $casts = [
        'progress_percent' => 'integer',
        'photo_paths' => 'array',
    ];

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
