<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmountToProjectsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('projects') && ! Schema::hasColumn('projects', 'amount')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->decimal('amount', 15, 2)->nullable()->after('name');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('projects') && Schema::hasColumn('projects', 'amount')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('amount');
            });
        }
    }
}