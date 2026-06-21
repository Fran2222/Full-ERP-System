<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubmissionFieldsToAttendanceRecordsTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attendance_records')) {
            return;
        }

        Schema::table('attendance_records', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_records', 'attendance_batch_key')) {
                $table->string('attendance_batch_key')->nullable()->after('remarks')->index();
            }

            if (!Schema::hasColumn('attendance_records', 'cutoff_month')) {
                $table->string('cutoff_month', 7)->nullable()->after('attendance_batch_key')->index();
            }

            if (!Schema::hasColumn('attendance_records', 'cutoff_period')) {
                $table->string('cutoff_period', 30)->nullable()->after('cutoff_month')->index();
            }

            if (!Schema::hasColumn('attendance_records', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('cutoff_period')->index();
            }

            if (!Schema::hasColumn('attendance_records', 'submitted_by')) {
                $table->unsignedBigInteger('submitted_by')->nullable()->after('submitted_at')->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('attendance_records')) {
            return;
        }

        Schema::table('attendance_records', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_records', 'submitted_by')) {
                $table->dropColumn('submitted_by');
            }

            if (Schema::hasColumn('attendance_records', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }

            if (Schema::hasColumn('attendance_records', 'cutoff_period')) {
                $table->dropColumn('cutoff_period');
            }

            if (Schema::hasColumn('attendance_records', 'cutoff_month')) {
                $table->dropColumn('cutoff_month');
            }

            if (Schema::hasColumn('attendance_records', 'attendance_batch_key')) {
                $table->dropColumn('attendance_batch_key');
            }
        });
    }
}
