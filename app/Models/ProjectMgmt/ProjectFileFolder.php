<?php

namespace App\Models\ProjectMgmt;

use Illuminate\Database\Eloquent\Model;

class ProjectFileFolder extends Model
{
    protected $table = 'project_file_folders';

    protected $fillable = [
        'project_id',
        'color',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
