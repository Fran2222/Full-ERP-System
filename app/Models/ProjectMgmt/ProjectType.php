<?php

namespace App\Models\ProjectMgmt;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProjectMgmt\Project;

class ProjectType extends Model
{
    use HasFactory;

    protected $table = 'project_types';

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class, 'project_type_id');
    }
}