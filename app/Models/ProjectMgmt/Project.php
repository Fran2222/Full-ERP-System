<?php

namespace App\Models\ProjectMgmt;

use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';

    protected $fillable = [
        'code',
        'name',
        'amount',
        'description',

        'branch_id',
        'department_id',
        'project_type',
        'status',
        'start_date',
        'end_date',

        'client_id',
        'project_type_id',
        'priority_id',
        'status_id',
        'project_manager_id',
        'location',
        'target_end_date',
        'actual_end_date',
        'progress_percent',
        'is_archived',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'target_end_date' => 'date',
        'actual_end_date' => 'date',
        'progress_percent' => 'integer',
        'is_archived' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_users', 'project_id', 'user_id')
            ->withTimestamps();
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function type()
    {
        return $this->belongsTo(ProjectType::class, 'project_type_id');
    }

    public function priority()
    {
        return $this->belongsTo(ProjectPriority::class, 'priority_id');
    }

    public function projectStatus()
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class);
    }
    public function files()
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function fileFolder()
    {
        return $this->hasOne(ProjectFileFolder::class);
    }

    public function projectFileActivities()
    {
        return $this->hasMany(ProjectFileActivity::class);
    }

}

