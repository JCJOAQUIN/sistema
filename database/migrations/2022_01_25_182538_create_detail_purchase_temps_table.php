<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetailPurchaseTempsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_purchase_temps', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('idPurchaseTemp')->unsigned();
            $table->decimal('quantity',20,2)->nullable();
            $table->text('unit')->nullable();
            $table->text('description')->nullable();
            $table->decimal('unitPrice',20,2)->nullable();
            $table->decimal('tax',20,2)->nullable();
            $table->decimal('discount',20,2)->nullable();
            $table->decimal('amount',20,2)->nullable();
            $table->string('typeTax',100)->nullable();
            $table->decimal('subtotal',20,2)->nullable();
            $table->integer('category')->nullable();
            $table->tinyInteger('statusWarehouse')->default(0);
            $table->text('commentaries')->nullable();
            $table->text('code')->nullable();
            $table->text('measurement')->nullable();
            $table->foreign('idPurchaseTemp')->references('id')->on('purchase_temps');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_purchase_temps');
    }
}
