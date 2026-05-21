<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationFormsTable extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->string('status')->default('draft'); // draft, active, archived
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluation_forms');
    }
}
