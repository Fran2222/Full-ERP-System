<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('overtime_requests')) {
            return;
        }

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            foreach ([
                'rate_per_hour',
                'daily_rate',
                'overtime_amount',
                'night_differential_amount',
                'amount',
                'total_amount',
            ] as $column) {
                if (Schema::hasColumn('overtime_requests', $column)) {
                    DB::statement("ALTER TABLE overtime_requests MODIFY {$column} DECIMAL(12,4) NULL");
                }
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('overtime_requests')) {
            return;
        }

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            foreach ([
                'rate_per_hour',
                'daily_rate',
                'overtime_amount',
                'night_differential_amount',
                'amount',
                'total_amount',
            ] as $column) {
                if (Schema::hasColumn('overtime_requests', $column)) {
                    DB::statement("ALTER TABLE overtime_requests MODIFY {$column} DECIMAL(12,2) NULL");
                }
            }
        }
    }
};
