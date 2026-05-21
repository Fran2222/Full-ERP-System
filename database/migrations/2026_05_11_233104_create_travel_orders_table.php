<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('order_date')->nullable();
            $table->string('to')->nullable();

            $table->json('employees_authorized')->nullable();

            $table->string('destination')->nullable();
            $table->text('purpose_a')->nullable();
            $table->text('purpose_b')->nullable();

            $table->date('travel_start_date')->nullable();
            $table->date('travel_end_date')->nullable();

            $table->text('remarks')->nullable();

            $table->string('status')->default('pending'); 
            // pending, approved, rejected

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_orders');
    }
};