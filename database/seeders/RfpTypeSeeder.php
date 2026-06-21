<?php

namespace Database\Seeders;

use App\Models\ProjectMgmt\RfpType;
use Illuminate\Database\Seeder;

class RfpTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['code' => 'RFP-RI', 'name' => 'Roughing Ins', 'description' => 'Payments related to roughing-in project activities.'],
            ['code' => 'RFP-TO', 'name' => 'Travel Orders', 'description' => 'Travel order budget requests.'],
            ['code' => 'RFP-MA', 'name' => 'Meal Allowances', 'description' => 'Meal allowance requests for project teams.'],
            ['code' => 'RFP-OTH', 'name' => 'Others', 'description' => 'Other project payment requests.'],
        ];

        foreach ($types as $type) {
            RfpType::firstOrCreate(['code' => $type['code']], $type + ['status' => 'active']);
        }
    }
}
