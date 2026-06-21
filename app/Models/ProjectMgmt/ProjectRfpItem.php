<?php

namespace App\Models\ProjectMgmt;

use Illuminate\Database\Eloquent\Model;

class ProjectRfpItem extends Model
{
    protected $table = 'project_rfp_items';

    protected $fillable = [
        'project_rfp_id',
        'description',
        'quantity',
        'unit',
        'unit_cost',
        'total_amount',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function rfp()
    {
        return $this->belongsTo(ProjectRfp::class, 'project_rfp_id');
    }
}
