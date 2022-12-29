<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscountsNominasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discounts_nominas', function (Blueprint $table)
        {
            $table->increments('id');
            $table->decimal('amount',20,2)->nullable();
            $table->text('reason')->nullable();
            $table->integer('idnominaemployeenf')->unsigned();
            $table->foreign('idnominaemployeenf')->references('idnominaemployeenf')->on('nomina_employee_n_fs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discounts_nominas');
    }
}
