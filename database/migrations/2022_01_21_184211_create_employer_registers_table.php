<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployerRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employer_registers', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('enterprise_id')->unsigned();
            $table->string('employer_register',100);
            $table->decimal('risk_number',20,6);
            $table->integer('position_risk_id')->unsigned();
            $table->foreign('enterprise_id')->references('id')->on('enterprises');
            $table->foreign('position_risk_id')->references('id')->on('cat_position_risks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employer_registers');
    }
}
