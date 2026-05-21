<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollItemsTable extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_run_id')
                ->constrained('payroll_runs')
                ->cascadeOnDelete();

            $table->foreignId('employee_profile_id')
                ->constrained('employee_profiles')
                ->cascadeOnDelete();

            $table->integer('present_days')->default(0);
            $table->integer('absent_days')->default(0);
            $table->decimal('worked_hours', 8, 2)->default(0);
            $table->integer('late_minutes')->default(0);
            $table->integer('undertime_minutes')->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);

            $table->decimal('daily_rate', 10, 2)->default(0);
            $table->decimal('basic_pay', 10, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('late_deduction', 10, 2)->default(0);
            $table->decimal('undertime_deduction', 10, 2)->default(0);
            $table->decimal('absence_deduction', 10, 2)->default(0);

            $table->decimal('sss', 10, 2)->default(0);
            $table->decimal('philhealth', 10, 2)->default(0);
            $table->decimal('pagibig', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);

            $table->decimal('allowance', 10, 2)->default(0);
            $table->decimal('gross_pay', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('net_pay', 10, 2)->default(0);

            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
}