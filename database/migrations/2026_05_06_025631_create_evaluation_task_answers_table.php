<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationTaskAnswersTable extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_task_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_task_id')
                ->constrained('evaluation_tasks')
                ->cascadeOnDelete();

            $table->foreignId('evaluation_form_question_id')
                ->constrained('evaluation_form_questions')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('score');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(
                ['evaluation_task_id', 'evaluation_form_question_id'],
                'evaluation_task_question_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_task_answers');
    }
}