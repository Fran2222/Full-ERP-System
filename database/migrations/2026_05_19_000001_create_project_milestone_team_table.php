<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_milestone_team')) {
            return;
        }

        Schema::create('project_milestone_team', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_milestone_id')
                ->constrained('project_milestones')
                ->cascadeOnDelete();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['project_milestone_id', 'team_id'], 'milestone_team_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_milestone_team');
    }
};
