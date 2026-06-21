<?php

namespace App\Models\ProjectMgmt;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProjectFileActivity extends Model
{
    protected $table = 'project_file_activities';

    protected $fillable = [
        'project_id',
        'project_file_id',
        'user_id',
        'action',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function file()
    {
        return $this->belongsTo(ProjectFile::class, 'project_file_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUserNameAttribute(): string
    {
        if (! $this->user) {
            return 'System';
        }

        return trim(($this->user->first_name ?? '') . ' ' . ($this->user->last_name ?? ''))
            ?: ($this->user->name ?? $this->user->email ?? 'System');
    }
}
