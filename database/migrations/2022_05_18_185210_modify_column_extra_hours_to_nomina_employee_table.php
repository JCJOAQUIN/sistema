<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnExtraHoursToNominaEmployeeTable extends Migration
{
    public function up()
    {
        Schema::table('nomina_employees', function (Blueprint $table)
        {
            $table->decimal('extra_hours',5,2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('nomina_employees', function (Blueprint $table)
        {
            $table->integer('extra_hours')->nullable()->change();
        });
    }
}
