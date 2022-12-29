<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKilometersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kilometers', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date_kilometer')->nullable();
            $table->string('kilometer',250)->nullable();
            $table->unsignedInteger('vehicles_id')->nullable();
            $table->timestamps();
            $table->foreign('vehicles_id')->references('id')->on('vehicles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kilometers');
    }
}
