<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents_warehouses', function (Blueprint $table)
        {
            $table->increments('iddocumentsWarehouse');
			$table->text('path')->nullable();
			$table->integer('idlot')->unsigned()->nullable();
			$table->tinyInteger('status')->default(1)->comment('1.- activo 2.- eliminado');
			$table->Foreign('idlot')->references('idlot')->on('lots');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents_warehouses');
    }
}
