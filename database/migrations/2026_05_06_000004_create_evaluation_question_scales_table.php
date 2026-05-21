<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationQuestionScalesTable extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_question_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_form_question_id')->constrained('evaluation_form_questions')->cascadeOnDelete();
            $table->string('label');
            $table->unsignedTinyInteger('min_score');
            $table->unsignedTinyInteger('max_score');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_question_scales');
    }
}
