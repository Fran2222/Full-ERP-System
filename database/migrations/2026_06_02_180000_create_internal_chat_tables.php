<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chat_conversations')) {
            Schema::create('chat_conversations', function (Blueprint $table) {
                $table->id();
                $table->string('type')->default('direct');
                $table->string('title')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['type', 'created_at']);
            });
        }

        if (! Schema::hasTable('chat_participants')) {
            Schema::create('chat_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('last_read_at')->nullable();
                $table->timestamps();

                $table->unique(['chat_conversation_id', 'user_id'], 'chat_participant_unique');
                $table->index(['user_id', 'last_read_at']);
            });
        }

        if (! Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
                $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
                $table->text('body');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['chat_conversation_id', 'created_at']);
                $table->index(['sender_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_participants');
        Schema::dropIfExists('chat_conversations');
    }
};