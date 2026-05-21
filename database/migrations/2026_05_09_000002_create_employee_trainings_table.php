<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->string('training_title');
            $table->string('provider')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('certificate_number')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('certificate_path')->nullable();
            $table->string('certificate_file_name')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_trainings');
    }
};
