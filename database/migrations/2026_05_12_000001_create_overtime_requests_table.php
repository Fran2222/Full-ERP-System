<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('department_head_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('date_filed');
            $table->text('reason');

            $table->date('overtime_date');
            $table->time('time_started');
            $table->time('time_ended');

            $table->string('gps_time_tracking_proof')->nullable();
            $table->string('work_output_proof')->nullable();

            $table->string('employee_certified_name')->nullable();
            $table->date('date_submitted')->nullable();

            $table->decimal('rate_per_hour', 12, 4)->nullable();
            $table->decimal('total_hours', 8, 2)->nullable();
            $table->text('computation')->nullable();
            $table->decimal('amount', 12, 4)->nullable();
            $table->decimal('total_amount', 12, 4)->nullable();
            $table->date('date_paid')->nullable();

            $table->string('status')->default('pending_department_head');
            // pending_department_head, department_head_rejected, pending_hr, approved, rejected

            $table->foreignId('department_head_reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('department_head_reviewed_at')->nullable();
            $table->text('department_head_remarks')->nullable();

            $table->foreignId('hr_reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('hr_reviewed_at')->nullable();
            $table->text('hr_remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};
