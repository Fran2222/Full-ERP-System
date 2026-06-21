<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_code', 50)->unique();
            $table->string('plate_number', 50)->nullable()->unique();
            $table->foreignId('vehicle_type_id')->nullable()->constrained('vehicle_types')->nullOnDelete();
            $table->foreignId('status_id')->nullable()->constrained('vehicle_statuses')->nullOnDelete();
            $table->foreignId('assigned_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('default_driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('brand', 150)->nullable();
            $table->string('model', 150)->nullable();
            $table->string('year_model', 20)->nullable();
            $table->string('color', 80)->nullable();
            $table->string('fuel_type', 80)->nullable();
            $table->string('engine_no', 150)->nullable();
            $table->string('chassis_no', 150)->nullable();
            $table->unsignedBigInteger('current_odometer')->default(0);
            $table->date('acquisition_date')->nullable();
            $table->decimal('acquisition_cost', 15, 2)->nullable();
            $table->string('photo_path')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status_id', 'assigned_branch_id']);
        });

        Schema::create('vehicle_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('project_site_text')->nullable();
            $table->text('purpose')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'ended', 'cancelled'])->default('active');
            $table->text('remarks')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vehicle_id', 'status']);
            $table->index(['driver_id', 'status']);
        });

        Schema::create('vehicle_assignment_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_assignment_id')->constrained('vehicle_assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_in_vehicle', 80)->nullable();
            $table->timestamps();

            $table->unique(['vehicle_assignment_id', 'user_id']);
        });

        Schema::create('vehicle_maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('maintenance_type_id')->nullable()->constrained('vehicle_maintenance_types')->nullOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('performed_by')->nullable();
            $table->date('maintenance_date');
            $table->unsignedBigInteger('odometer')->nullable();
            $table->text('issue_or_concern')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('parts_replaced')->nullable();
            $table->string('shop_or_mechanic')->nullable();
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->decimal('parts_cost', 15, 2)->default(0);
            $table->decimal('other_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->date('next_maintenance_date')->nullable();
            $table->unsignedBigInteger('next_maintenance_odometer')->nullable();
            $table->enum('status', ['open', 'in_progress', 'completed', 'cancelled'])->default('open');
            $table->string('attachment_path')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vehicle_id', 'maintenance_date']);
            $table->index(['status', 'next_maintenance_date']);
        });

        Schema::create('vehicle_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('document_type', 120);
            $table->string('document_no', 150)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedInteger('reminder_days_before')->default(30);
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['active', 'expired', 'renewed', 'cancelled'])->default('active');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vehicle_id', 'document_type']);
            $table->index(['expiry_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_documents');
        Schema::dropIfExists('vehicle_maintenance_records');
        Schema::dropIfExists('vehicle_assignment_members');
        Schema::dropIfExists('vehicle_assignments');
        Schema::dropIfExists('vehicles');
    }
};
