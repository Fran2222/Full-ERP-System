<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceJobOrderReportsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('service_job_order_reports')) {
            Schema::create('service_job_order_reports', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_job_order_id')->index();
                $table->unsignedBigInteger('reported_by')->nullable()->index();
                $table->dateTime('reported_at')->nullable();
                $table->text('findings')->nullable();
                $table->text('work_done')->nullable();
                $table->text('recommendation')->nullable();
                $table->string('customer_acknowledged_by')->nullable();
                $table->string('status_after_report')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('service_job_order_photos')) {
            Schema::create('service_job_order_photos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_job_order_report_id')->index();
                $table->string('file_path');
                $table->string('original_name')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('service_job_order_photos');
        Schema::dropIfExists('service_job_order_reports');
    }
}
