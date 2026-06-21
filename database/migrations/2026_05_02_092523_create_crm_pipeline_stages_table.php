<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmPipelineStagesTable extends Migration
{
    public function up()
    {
        Schema::create('crm_pipeline_stages', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            // For ordering columns left to right
            $table->integer('position')->default(0);

            // For UI color indicator: primary, info, success, warning, danger, secondary, dark
            $table->string('color')->default('primary');

            // Default stages like New Leads, Contacted, Won, Lost
            $table->boolean('is_default')->default(false);

            // Locked stages cannot be deleted accidentally
            $table->boolean('is_locked')->default(false);

            // active / inactive
            $table->string('status')->default('active');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('crm_pipeline_stages');
    }
}