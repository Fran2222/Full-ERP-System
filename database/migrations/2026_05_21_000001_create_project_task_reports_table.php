<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_task_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->text('report_details');
            $table->json('photo_paths')->nullable();
            $table->timestamps();

            $table->index(['project_task_id', 'reported_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_reports');
    }
};
