<?php

namespace App\Models\ProjectMgmt;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_type_id',
        'title',
        'start_date',
        'end_date',
        'task_time',
        'location',
        'description',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function projectType()
    {
        return $this->belongsTo(ProjectType::class, 'project_type_id');
    }

    public static function typeColorFor(mixed $seed): string
    {
        $palette = [
            '#3a57e8',
            '#08b1ba',
            '#1aa053',
            '#f16a1b',
            '#d63384',
            '#6f42c1',
            '#0d6efd',
            '#dc3545',
            '#198754',
            '#fd7e14',
        ];

        $index = (int) (sprintf('%u', crc32((string) $seed)) % count($palette));

        return $palette[$index];
    }

    public function getTypeColorAttribute(): string
    {
        $seed = $this->project_type_id;

        if ($this->relationLoaded('projectType') && $this->projectType) {
            $seed = $this->projectType->code ?: $this->projectType->name ?: $this->project_type_id;
        }

        return self::typeColorFor($seed);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'project_task_team', 'project_task_id', 'team_id')
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reports()
    {
        return $this->hasMany(ProjectTaskReport::class, 'project_task_id')->latest();
    }
}

