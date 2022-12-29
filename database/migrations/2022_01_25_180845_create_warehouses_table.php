<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouses', function (Blueprint $table)
        {
            $table->increments('idwarehouse');
            $table->integer('concept')->unsigned()->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('quantityReal')->nullable();
            $table->text('short_code')->nullable();
            $table->text('long_code')->nullable();
            $table->integer('measurement')->nullable();
            $table->text('commentaries')->nullable();
            $table->decimal('amountUnit',20,2)->nullable();
            $table->string('typeTax',50)->nullable();
            $table->decimal('iva',20,2)->nullable();
            $table->decimal('subtotal',20,2)->nullable();
            $table->decimal('amount',20,2)->nullable();
            $table->integer('idLot')->unsigned()->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('warehouseType')->unsigned()->nullable();
            $table->integer('place_location')->unsigned()->nullable();
            $table->integer('account')->nullable();
            $table->integer('type')->nullable();
            $table->text('brand')->nullable();
            $table->text('storage')->nullable();
            $table->text('processor')->nullable();
            $table->text('ram')->nullable();
            $table->text('sku')->nullable();
            $table->integer('damaged')->nullable();
            $table->foreign('concept')->references('id')->on('cat_warehouse_concepts');
            $table->foreign('idLot')->references('idlot')->on('lots');
            $table->foreign('warehouseType')->references('id')->on('cat_warehouse_types');
            $table->foreign('place_location')->references('id')->on('places');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouses');
    }
}
