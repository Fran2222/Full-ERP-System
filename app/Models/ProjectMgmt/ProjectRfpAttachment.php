<?php

namespace App\Models\ProjectMgmt;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProjectRfpAttachment extends Model
{
    protected $table = 'project_rfp_attachments';

    protected $fillable = [
        'project_rfp_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    public function rfp()
    {
        return $this->belongsTo(ProjectRfp::class, 'project_rfp_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
