<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProjectFinanceRfpTables extends Migration
{
    public function up()
    {
        Schema::create('rfp_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('rfp_types')->insert([
            ['code' => 'RFP-RI', 'name' => 'Roughing Ins', 'description' => 'Payments related to roughing-in project activities.', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'RFP-TO', 'name' => 'Travel Orders', 'description' => 'Travel order budget requests.', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'RFP-MA', 'name' => 'Meal Allowances', 'description' => 'Meal allowance requests for project teams.', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'RFP-OTH', 'name' => 'Others', 'description' => 'Other project payment requests.', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('project_rfps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfp_type_id')->constrained('rfp_types')->restrictOnDelete();
            $table->string('rfp_code')->unique();
            $table->unsignedInteger('sequence_no');
            $table->date('date_requested');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payee_name')->nullable();

            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('project_code_snapshot')->nullable();
            $table->string('project_name_snapshot')->nullable();
            $table->decimal('project_amount_snapshot', 15, 2)->nullable();

            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('client_name_snapshot')->nullable();
            $table->string('client_contact_snapshot')->nullable();
            $table->text('client_address_snapshot')->nullable();

            $table->text('request_details');
            $table->decimal('requested_total_amount', 15, 2)->default(0);
            $table->decimal('actual_released_amount', 15, 2)->nullable();
            $table->date('date_released')->nullable();
            $table->string('cash_voucher_no')->nullable();
            $table->string('status', 30)->default('pending');

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['rfp_type_id', 'sequence_no']);
            $table->index(['status', 'date_requested']);
        });

        Schema::create('project_rfp_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_rfp_id')->constrained('project_rfps')->cascadeOnDelete();
            $table->text('description');
            $table->decimal('quantity', 12, 2)->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('project_rfp_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_rfp_id')->constrained('project_rfps')->cascadeOnDelete();
            $table->string('step_name');
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('project_rfp_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_rfp_id')->constrained('project_rfps')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_rfp_attachments');
        Schema::dropIfExists('project_rfp_approvals');
        Schema::dropIfExists('project_rfp_items');
        Schema::dropIfExists('project_rfps');
        Schema::dropIfExists('rfp_types');
    }
}
