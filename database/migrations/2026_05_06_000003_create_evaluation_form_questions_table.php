<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationFormQuestionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_form_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_form_section_id')->constrained('evaluation_form_sections')->cascadeOnDelete();
            $table->string('title');
            $table->text('question')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_form_questions');
    }
}
