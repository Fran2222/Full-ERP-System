<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateVehicleDocumentTypesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('vehicle_document_types')) {
            Schema::create('vehicle_document_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code')->nullable()->unique();
                $table->text('description')->nullable();
                $table->string('status')->default('active');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        $defaults = [
            'OR/CR',
            'Registration',
            'Insurance',
            'Emission Test',
            'Permit',
            'Franchise',
            'Maintenance Receipt',
            'Other',
        ];

        foreach ($defaults as $name) {
            DB::table('vehicle_document_types')->updateOrInsert(
                ['name' => $name],
                [
                    'code' => strtoupper(str_replace([' ', '/', '-'], '_', $name)),
                    'status' => 'active',
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_document_types');
    }
}
