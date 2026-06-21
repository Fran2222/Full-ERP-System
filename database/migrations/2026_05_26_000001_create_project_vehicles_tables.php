<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sequence_no')->unique();
            $table->string('vehicle_code')->unique();
            $table->string('plate_name');
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('project_vehicle_drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_vehicle_id')->constrained('project_vehicles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['project_vehicle_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_vehicle_drivers');
        Schema::dropIfExists('project_vehicles');
    }
};
