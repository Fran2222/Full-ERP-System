<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'approval_flow')) {
                $table->json('approval_flow')->nullable()->after('status');
            }

            if (!Schema::hasColumn('leave_requests', 'current_approval_step')) {
                $table->string('current_approval_step')->nullable()->after('approval_flow');
            }

            if (!Schema::hasColumn('leave_requests', 'department_head_id')) {
                $table->foreignId('department_head_id')->nullable()->after('current_approval_step')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('leave_requests', 'department_head_status')) {
                $table->string('department_head_status')->default('skipped')->after('department_head_id');
            }

            if (!Schema::hasColumn('leave_requests', 'department_head_reviewed_by')) {
                $table->foreignId('department_head_reviewed_by')->nullable()->after('department_head_status')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('leave_requests', 'department_head_reviewed_at')) {
                $table->dateTime('department_head_reviewed_at')->nullable()->after('department_head_reviewed_by');
            }

            if (!Schema::hasColumn('leave_requests', 'department_head_notes')) {
                $table->text('department_head_notes')->nullable()->after('department_head_reviewed_at');
            }

            if (!Schema::hasColumn('leave_requests', 'hr_status')) {
                $table->string('hr_status')->default('pending')->after('department_head_notes');
            }

            if (!Schema::hasColumn('leave_requests', 'hr_reviewed_by')) {
                $table->foreignId('hr_reviewed_by')->nullable()->after('hr_status')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('leave_requests', 'hr_reviewed_at')) {
                $table->dateTime('hr_reviewed_at')->nullable()->after('hr_reviewed_by');
            }

            if (!Schema::hasColumn('leave_requests', 'hr_notes')) {
                $table->text('hr_notes')->nullable()->after('hr_reviewed_at');
            }

            if (!Schema::hasColumn('leave_requests', 'admin_status')) {
                $table->string('admin_status')->default('pending')->after('hr_notes');
            }

            if (!Schema::hasColumn('leave_requests', 'admin_reviewed_by')) {
                $table->foreignId('admin_reviewed_by')->nullable()->after('admin_status')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('leave_requests', 'admin_reviewed_at')) {
                $table->dateTime('admin_reviewed_at')->nullable()->after('admin_reviewed_by');
            }

            if (!Schema::hasColumn('leave_requests', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('admin_reviewed_at');
            }
        });

        DB::table('leave_requests')
            ->whereNull('approval_flow')
            ->update([
                'approval_flow' => json_encode(['hr', 'admin']),
                'current_approval_step' => DB::raw("CASE WHEN status = 'pending' THEN 'hr' ELSE NULL END"),
                'department_head_status' => 'skipped',
                'hr_status' => DB::raw("CASE WHEN status = 'approved' THEN 'approved' WHEN status = 'rejected' THEN 'rejected' ELSE 'pending' END"),
                'admin_status' => DB::raw("CASE WHEN status = 'approved' THEN 'approved' WHEN status = 'rejected' THEN 'rejected' ELSE 'pending' END"),
            ]);
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $columns = [
                'admin_notes',
                'admin_reviewed_at',
                'admin_reviewed_by',
                'admin_status',
                'hr_notes',
                'hr_reviewed_at',
                'hr_reviewed_by',
                'hr_status',
                'department_head_notes',
                'department_head_reviewed_at',
                'department_head_reviewed_by',
                'department_head_status',
                'department_head_id',
                'current_approval_step',
                'approval_flow',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('leave_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
