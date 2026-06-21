<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_gas_slips', function (Blueprint $table) {
            $table->id();
            $table->string('po_no');
            $table->foreignId('project_vehicle_id')->constrained('project_vehicles')->restrictOnDelete();
            $table->string('location')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('issued_date');
            $table->timestamp('returned_date')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_original_name')->nullable();
            $table->string('attachment_mime_type')->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->string('status', 30)->default('issued');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('project_gas_slip_drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_gas_slip_id')->constrained('project_gas_slips')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['project_gas_slip_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_gas_slip_drivers');
        Schema::dropIfExists('project_gas_slips');
    }
};
