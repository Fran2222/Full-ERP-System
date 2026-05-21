<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRecordsTable extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_profile_id')
                ->constrained('employee_profiles')
                ->cascadeOnDelete();

            $table->date('attendance_date');

            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();

            $table->decimal('break_hours', 5, 2)->default(1.00);
            $table->integer('late_minutes')->default(0);
            $table->integer('undertime_minutes')->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->decimal('total_worked_hours', 5, 2)->default(0);

            $table->enum('status', [
                'present',
                'absent',
                'late',
                'half_day',
                'leave'
            ])->default('present');

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique(['employee_profile_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
}