<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('project_type_id')->constrained('project_types')->restrictOnDelete();
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('task_time')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('project_task_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_task_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_team');
        Schema::dropIfExists('project_tasks');
    }
};
