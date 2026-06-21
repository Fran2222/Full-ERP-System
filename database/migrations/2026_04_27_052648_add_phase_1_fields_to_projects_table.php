<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhase1FieldsToProjectsTable extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'code')) {
                $table->string('code')->nullable()->unique();
            }

            if (!Schema::hasColumn('projects', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable();
            }

            if (!Schema::hasColumn('projects', 'project_type_id')) {
                $table->unsignedBigInteger('project_type_id')->nullable();
            }

            if (!Schema::hasColumn('projects', 'priority_id')) {
                $table->unsignedBigInteger('priority_id')->nullable();
            }

            if (!Schema::hasColumn('projects', 'status_id')) {
                $table->unsignedBigInteger('status_id')->nullable();
            }

            if (!Schema::hasColumn('projects', 'project_manager_id')) {
                $table->foreignId('project_manager_id')->nullable()->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('projects', 'location')) {
                $table->string('location')->nullable();
            }

            if (!Schema::hasColumn('projects', 'target_end_date')) {
                $table->date('target_end_date')->nullable();
            }

            if (!Schema::hasColumn('projects', 'actual_end_date')) {
                $table->date('actual_end_date')->nullable();
            }

            if (!Schema::hasColumn('projects', 'progress_percent')) {
                $table->unsignedTinyInteger('progress_percent')->default(0);
            }

            if (!Schema::hasColumn('projects', 'is_archived')) {
                $table->boolean('is_archived')->default(false);
            }

            if (!Schema::hasColumn('projects', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'project_manager_id')) {
                $table->dropConstrainedForeignId('project_manager_id');
            }

            if (Schema::hasColumn('projects', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            $columns = [
                'code',
                'client_id',
                'project_type_id',
                'priority_id',
                'status_id',
                'location',
                'target_end_date',
                'actual_end_date',
                'progress_percent',
                'is_archived',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('projects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}