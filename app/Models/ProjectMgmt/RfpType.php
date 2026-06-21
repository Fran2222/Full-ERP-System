<?php

namespace App\Models\ProjectMgmt;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfpType extends Model
{
    use HasFactory;

    protected $table = 'rfp_types';

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    public function rfps()
    {
        return $this->hasMany(ProjectRfp::class, 'rfp_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
