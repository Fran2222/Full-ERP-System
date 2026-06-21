<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chat_message_attachments')) {
            Schema::create('chat_message_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_message_id')->constrained('chat_messages')->cascadeOnDelete();
                $table->string('disk')->default('public');
                $table->string('path');
                $table->string('original_name');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->timestamps();

                $table->index(['chat_message_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_message_attachments');
    }
};