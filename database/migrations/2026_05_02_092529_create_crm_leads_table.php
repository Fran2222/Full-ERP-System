<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmLeadsTable extends Migration
{
    public function up()
    {
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();

            $table->string('lead_code')->unique();

            // Kanban column / pipeline stage
            $table->foreignId('stage_id')
                ->nullable()
                ->constrained('crm_pipeline_stages')
                ->nullOnDelete();

            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();

            // referral, facebook, website, walk-in, email, phone call, existing client, others
            $table->string('source')->nullable();

            // low, medium, high, urgent
            $table->string('priority')->default('medium');

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->decimal('estimated_value', 15, 2)->nullable();

            $table->date('expected_close_date')->nullable();
            $table->date('next_follow_up_date')->nullable();

            $table->text('notes')->nullable();

            // Future links to existing Wizmaster records
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();

            $table->timestamp('converted_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['stage_id', 'priority']);
            $table->index(['assigned_to', 'next_follow_up_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('crm_leads');
    }
}