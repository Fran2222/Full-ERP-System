<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceBatchesTable extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance_batches')) {
            Schema::table('attendance_batches', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_batches', 'batch_no')) {
                    $table->string('batch_no')->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('attendance_batches', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('batch_no')->index();
                }
                if (!Schema::hasColumn('attendance_batches', 'month')) {
                    $table->string('month', 7)->nullable()->after('branch_id')->index();
                }
                if (!Schema::hasColumn('attendance_batches', 'cutoff_period')) {
                    $table->string('cutoff_period', 30)->nullable()->after('month')->index();
                }
                if (!Schema::hasColumn('attendance_batches', 'cutoff_start')) {
                    $table->date('cutoff_start')->nullable()->after('cutoff_period');
                }
                if (!Schema::hasColumn('attendance_batches', 'cutoff_end')) {
                    $table->date('cutoff_end')->nullable()->after('cutoff_start');
                }
                if (!Schema::hasColumn('attendance_batches', 'total_employees')) {
                    $table->unsignedInteger('total_employees')->default(0)->after('cutoff_end');
                }
                if (!Schema::hasColumn('attendance_batches', 'status')) {
                    $table->string('status', 30)->default('draft')->after('total_employees')->index();
                }
                if (!Schema::hasColumn('attendance_batches', 'prepared_by')) {
                    $table->unsignedBigInteger('prepared_by')->nullable()->after('status')->index();
                }
                if (!Schema::hasColumn('attendance_batches', 'submitted_by')) {
                    $table->unsignedBigInteger('submitted_by')->nullable()->after('prepared_by')->index();
                }
                if (!Schema::hasColumn('attendance_batches', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable()->after('submitted_by');
                }
                if (!Schema::hasColumn('attendance_batches', 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable()->after('submitted_at')->index();
                }
                if (!Schema::hasColumn('attendance_batches', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('attendance_batches', 'posted_by')) {
                    $table->unsignedBigInteger('posted_by')->nullable()->after('approved_at')->index();
                }
                if (!Schema::hasColumn('attendance_batches', 'posted_at')) {
                    $table->timestamp('posted_at')->nullable()->after('posted_by');
                }
                if (!Schema::hasColumn('attendance_batches', 'remarks')) {
                    $table->text('remarks')->nullable()->after('posted_at');
                }
            });

            return;
        }

        Schema::create('attendance_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_no')->unique();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('month', 7)->index();
            $table->string('cutoff_period', 30)->index();
            $table->date('cutoff_start');
            $table->date('cutoff_end');
            $table->unsignedInteger('total_employees')->default(0);
            $table->string('status', 30)->default('draft')->index();
            $table->unsignedBigInteger('prepared_by')->nullable()->index();
            $table->unsignedBigInteger('submitted_by')->nullable()->index();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable()->index();
            $table->timestamp('posted_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'month', 'cutoff_period'], 'attendance_batches_branch_month_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_batches');
    }
}
