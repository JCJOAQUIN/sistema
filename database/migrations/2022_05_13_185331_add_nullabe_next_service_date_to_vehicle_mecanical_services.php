<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullabeNextServiceDateToVehicleMecanicalServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_mechanical_services', function (Blueprint $table)
		{
            $table->date('next_service_date')->nullable()->change();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_mechanical_services', function (Blueprint $table)
		{
            $table->date('next_service_date')->nullable(false)->change();
		});
    }
}
