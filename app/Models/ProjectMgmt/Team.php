<?php

namespace App\Models\ProjectMgmt;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'team_leader_id',
        'remarks',
        'status',
    ];

    public function teamLeader()
    {
        return $this->belongsTo(User::class, 'team_leader_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_team_members', 'team_id', 'user_id')
            ->withTimestamps();
    }

    public function milestones()
    {
        return $this->belongsToMany(
            ProjectMilestone::class,
            'project_milestone_team',
            'team_id',
            'project_milestone_id'
        )->withTimestamps();
    }

    public function tasks()
    {
        return $this->belongsToMany(
            ProjectTask::class,
            'project_task_team',
            'team_id',
            'project_task_id'
        )->withTimestamps();
    }
}

