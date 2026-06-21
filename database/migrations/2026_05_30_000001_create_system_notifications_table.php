<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemNotificationsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('system_notifications')) {
            return;
        }

        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('module', 80)->nullable()->index();
            $table->string('type', 120)->nullable()->index();
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('action_url')->nullable();
            $table->string('related_type')->nullable()->index();
            $table->unsignedBigInteger('related_id')->nullable()->index();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['related_type', 'related_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_notifications');
    }
}