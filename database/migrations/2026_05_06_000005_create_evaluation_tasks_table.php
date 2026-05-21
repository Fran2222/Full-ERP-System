<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationTasksTable extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_form_id')->constrained('evaluation_forms')->cascadeOnDelete();
            $table->string('title');
            $table->date('due_date')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('assigned_to_employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->foreignId('evaluator_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, submitted, completed, cancelled
            $table->decimal('performance_score', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_tasks');
    }
}
