<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExpandAttendanceRecordStatusValues extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attendance_records') || !Schema::hasColumn('attendance_records', 'status')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE attendance_records MODIFY status VARCHAR(30) NOT NULL DEFAULT 'present'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE attendance_records ALTER COLUMN status TYPE VARCHAR(30)");
            DB::statement("ALTER TABLE attendance_records ALTER COLUMN status SET DEFAULT 'present'");
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE attendance_records ALTER COLUMN status VARCHAR(30) NOT NULL");
        }
    }

    public function down(): void
    {
        // Intentionally left as no-op to avoid breaking existing rows using
        // leave_wop or holiday statuses after this migration has been applied.
    }
}
