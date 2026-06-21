<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->foreignId('team_id')
                ->nullable()
                ->constrained('teams')
                ->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->date('start_date');
            $table->date('end_date');

            $table->unsignedTinyInteger('weight_percent')->default(0);

            $table->string('status')->default('pending');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
    }
};