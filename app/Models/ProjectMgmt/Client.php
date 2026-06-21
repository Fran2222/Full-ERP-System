<?php

namespace App\Models\ProjectMgmt;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'contact_number',
        'email',
        'address',
        'remarks',
        'status',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
