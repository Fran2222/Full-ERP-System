<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_rfp_id');
            $table->foreignId('store_name_id')->constrained('store_names')->restrictOnDelete();
            $table->decimal('receipts_total_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('pending');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_original_name')->nullable();
            $table->string('attachment_mime_type')->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            if (Schema::hasTable('project_rfps')) {
                $table->foreign('project_rfp_id')->references('id')->on('project_rfps')->restrictOnDelete();
            }
        });

        Schema::create('project_expense_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_expense_id')->constrained('project_expenses')->cascadeOnDelete();
            $table->string('store_receipt_no');
            $table->date('store_receipt_date');
            $table->decimal('receipts_total_amount', 15, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_expense_receipts');
        Schema::dropIfExists('project_expenses');
    }
};
