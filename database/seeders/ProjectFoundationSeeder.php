<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\ProjectMgmt\Client;
use App\Models\ProjectMgmt\ProjectType;
use App\Models\ProjectMgmt\ProjectPriority;
use App\Models\ProjectMgmt\ProjectStatus;

class ProjectFoundationSeeder extends Seeder
{
    public function run()
    {
        // =========================
        // PROJECT TYPES
        // =========================
        $types = [
            ['code' => 'LGU', 'name' => 'LGU'],
            ['code' => 'COM', 'name' => 'Commercial'],
            ['code' => 'RES', 'name' => 'Residential'],
            ['code' => 'OTH', 'name' => 'Others'],
        ];

        foreach ($types as $type) {
            ProjectType::firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'status' => 'active',
                ]
            );
        }

        // =========================
        // PRIORITIES
        // =========================
        $priorities = [
            ['code' => 'LOW', 'name' => 'Low', 'level' => 1],
            ['code' => 'MED', 'name' => 'Medium', 'level' => 2],
            ['code' => 'HIGH', 'name' => 'High', 'level' => 3],
            ['code' => 'URG', 'name' => 'Urgent', 'level' => 4],
        ];

        foreach ($priorities as $priority) {
            ProjectPriority::firstOrCreate(
                ['code' => $priority['code']],
                [
                    'name' => $priority['name'],
                    'level' => $priority['level'],
                ]
            );
        }

        // =========================
        // STATUSES
        // =========================
        $statuses = [
            ['code' => 'PENDING', 'name' => 'Pending', 'color' => 'secondary', 'sort_order' => 1],
            ['code' => 'ONGOING', 'name' => 'Ongoing', 'color' => 'primary', 'sort_order' => 2],
            ['code' => 'ON_HOLD', 'name' => 'On Hold', 'color' => 'warning', 'sort_order' => 3],
            ['code' => 'COMPLETED', 'name' => 'Completed', 'color' => 'success', 'sort_order' => 4],
            ['code' => 'CANCELLED', 'name' => 'Cancelled', 'color' => 'danger', 'sort_order' => 5],
        ];

        foreach ($statuses as $status) {
            ProjectStatus::firstOrCreate(
                ['code' => $status['code']],
                [
                    'name' => $status['name'],
                    'color' => $status['color'],
                    'sort_order' => $status['sort_order'],
                ]
            );
        }

        // =========================
        // DEFAULT CLIENT
        // =========================
        Client::firstOrCreate(
            ['code' => 'DEFAULT'],
            [
                'name' => 'Default Client',
                'status' => 'active',
            ]
        );
    }
}
