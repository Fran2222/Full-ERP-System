<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSetupColumnsToProjectStatusesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('project_statuses')) {
            if (!Schema::hasColumn('project_statuses', 'description')) {
                Schema::table('project_statuses', function (Blueprint $table) {
                    $table->text('description')->nullable();
                });
            }

            if (!Schema::hasColumn('project_statuses', 'status')) {
                Schema::table('project_statuses', function (Blueprint $table) {
                    $table->string('status')->default('active');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('project_statuses')) {
            Schema::table('project_statuses', function (Blueprint $table) {
                if (Schema::hasColumn('project_statuses', 'description')) {
                    $table->dropColumn('description');
                }

                if (Schema::hasColumn('project_statuses', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
}