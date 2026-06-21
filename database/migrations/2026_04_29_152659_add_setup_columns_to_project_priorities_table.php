<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSetupColumnsToProjectPrioritiesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('project_priorities')) {
            if (!Schema::hasColumn('project_priorities', 'description')) {
                Schema::table('project_priorities', function (Blueprint $table) {
                    $table->text('description')->nullable();
                });
            }

            if (!Schema::hasColumn('project_priorities', 'status')) {
                Schema::table('project_priorities', function (Blueprint $table) {
                    $table->string('status')->default('active');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('project_priorities')) {
            Schema::table('project_priorities', function (Blueprint $table) {
                if (Schema::hasColumn('project_priorities', 'description')) {
                    $table->dropColumn('description');
                }

                if (Schema::hasColumn('project_priorities', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
}