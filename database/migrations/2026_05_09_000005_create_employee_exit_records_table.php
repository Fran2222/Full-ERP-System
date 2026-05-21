<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_exit_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->date('resignation_date')->nullable();
            $table->date('last_working_day')->nullable();
            $table->string('exit_type')->default('resignation');
            $table->text('reason')->nullable();
            $table->string('clearance_status')->default('not_started');
            $table->string('final_pay_status')->default('not_started');
            $table->boolean('rehire_eligibility')->nullable();
            $table->text('remarks')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_file_name')->nullable();
            $table->foreignId('encoded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_exit_records');
    }
};
