<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateVehicleFuelTypesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('vehicle_fuel_types')) {
            Schema::create('vehicle_fuel_types', function (Blueprint $table) {
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
            'Diesel',
            'Gasoline',
            'Electric',
            'Hybrid',
        ];

        foreach ($defaults as $name) {
            DB::table('vehicle_fuel_types')->updateOrInsert(
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
        Schema::dropIfExists('vehicle_fuel_types');
    }
}
