<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateServiceOperationsSetupTables extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('service_types')) {
            Schema::create('service_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('service_statuses')) {
            Schema::create('service_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('color')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_closed')->default(false);
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        $now = now();

        foreach ([
            'CCTV Installation',
            'CCTV Repair',
            'Computer Repair',
            'Network Troubleshooting',
            'Printer Service',
            'System Support',
            'Site Visit',
            'Warranty Support',
        ] as $type) {
            if (Schema::hasTable('service_types') && ! DB::table('service_types')->where('name', $type)->exists()) {
                DB::table('service_types')->insert([
                    'name' => $type,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $statuses = [
            ['Pending', 'secondary', 1, false],
            ['Scheduled', 'info', 2, false],
            ['Ongoing', 'primary', 3, false],
            ['Completed', 'success', 4, true],
            ['Cancelled', 'danger', 5, true],
        ];

        foreach ($statuses as $status) {
            if (Schema::hasTable('service_statuses') && ! DB::table('service_statuses')->where('name', $status[0])->exists()) {
                DB::table('service_statuses')->insert([
                    'name' => $status[0],
                    'color' => $status[1],
                    'sort_order' => $status[2],
                    'is_closed' => $status[3],
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('service_statuses');
        Schema::dropIfExists('service_types');
    }
}
