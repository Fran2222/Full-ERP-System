<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectFoundationTables extends Migration
{
    public function up()
    {
        // =========================
        // CLIENTS
        // =========================
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // =========================
        // PROJECT TYPES
        // =========================
        Schema::create('project_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // =========================
        // PROJECT PRIORITIES
        // =========================
        Schema::create('project_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedTinyInteger('level')->default(1);
            $table->timestamps();
        });

        // =========================
        // PROJECT STATUSES
        // =========================
        Schema::create('project_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('color')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->timestamps();
        });

        // =========================
        // ADD FOREIGN KEYS TO PROJECTS
        // =========================
        Schema::table('projects', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('project_type_id')->references('id')->on('project_types')->nullOnDelete();
            $table->foreign('priority_id')->references('id')->on('project_priorities')->nullOnDelete();
            $table->foreign('status_id')->references('id')->on('project_statuses')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['project_type_id']);
            $table->dropForeign(['priority_id']);
            $table->dropForeign(['status_id']);
        });

        Schema::dropIfExists('project_statuses');
        Schema::dropIfExists('project_priorities');
        Schema::dropIfExists('project_types');
        Schema::dropIfExists('clients');
    }
}