<?php

namespace Database\Seeders;

use App\Models\CrmPipelineStage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CrmPipelineStageSeeder extends Seeder
{
    public function run()
    {
        $stages = [
            [
                'name' => 'New Leads',
                'slug' => 'new-leads',
                'position' => 1,
                'color' => 'primary',
            ],
            [
                'name' => 'Contacted',
                'slug' => 'contacted',
                'position' => 2,
                'color' => 'info',
            ],
            [
                'name' => 'Qualified',
                'slug' => 'qualified',
                'position' => 3,
                'color' => 'success',
            ],
            [
                'name' => 'Proposal Sent',
                'slug' => 'proposal-sent',
                'position' => 4,
                'color' => 'warning',
            ],
            [
                'name' => 'Negotiation',
                'slug' => 'negotiation',
                'position' => 5,
                'color' => 'secondary',
            ],
            [
                'name' => 'Won',
                'slug' => 'won',
                'position' => 6,
                'color' => 'success',
            ],
            [
                'name' => 'Lost',
                'slug' => 'lost',
                'position' => 7,
                'color' => 'danger',
            ],
        ];

        foreach ($stages as $stage) {
            CrmPipelineStage::firstOrCreate(
                ['slug' => $stage['slug']],
                [
                    'name' => $stage['name'],
                    'position' => $stage['position'],
                    'color' => $stage['color'],
                    'is_default' => true,
                    'is_locked' => in_array($stage['slug'], ['new-leads', 'won', 'lost']),
                    'status' => 'active',
                ]
            );
        }
    }
}