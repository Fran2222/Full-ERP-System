<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaskTitleToEvaluationFormsTable extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_forms', function (Blueprint $table) {
            if (!Schema::hasColumn('evaluation_forms', 'task_title')) {
                $table->string('task_title')->nullable()->after('title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_forms', function (Blueprint $table) {
            if (Schema::hasColumn('evaluation_forms', 'task_title')) {
                $table->dropColumn('task_title');
            }
        });
    }
}