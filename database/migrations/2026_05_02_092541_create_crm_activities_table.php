<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmActivitiesTable extends Migration
{
    public function up()
    {
        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('crm_lead_id')
                ->constrained('crm_leads')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // note, created, updated, stage_changed, follow_up_added, follow_up_completed, converted
            $table->string('activity_type')->default('note');

            $table->text('description');

            // For tracking movement from one column to another
            $table->unsignedBigInteger('old_stage_id')->nullable();
            $table->unsignedBigInteger('new_stage_id')->nullable();

            $table->timestamps();

            $table->index(['crm_lead_id', 'activity_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('crm_activities');
    }
}