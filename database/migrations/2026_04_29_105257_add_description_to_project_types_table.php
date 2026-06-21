<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToProjectTypesTable extends Migration
{
    public function up()
    {
        Schema::table('project_types', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table('project_types', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}