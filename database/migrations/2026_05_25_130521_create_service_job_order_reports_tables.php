<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceJobOrderReportsTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('service_job_order_reports')) {
            Schema::create('service_job_order_reports', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_job_order_id');
                $table->unsignedBigInteger('reported_by_user_id')->nullable();
                $table->unsignedBigInteger('status_update_id')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->text('findings')->nullable();
                $table->text('work_done')->nullable();
                $table->text('recommendations')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index('service_job_order_id', 'sjor_job_order_idx');
                $table->index('reported_by_user_id', 'sjor_reported_by_idx');
                $table->index('status_update_id', 'sjor_status_update_idx');
            });
        }

        if (!Schema::hasTable('service_job_order_report_photos')) {
            Schema::create('service_job_order_report_photos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_job_order_report_id');
                $table->string('file_path');
                $table->string('original_name')->nullable();
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->timestamps();

                $table->index('service_job_order_report_id', 'sjorp_report_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('service_job_order_report_photos');
        Schema::dropIfExists('service_job_order_reports');
    }
}