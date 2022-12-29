<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToPurchaseTempsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_temps', function (Blueprint $table)
        {
            $table->integer('provider_data_id')->unsigned()->nullable();
            $table->foreign('provider_data_id')->references('id')->on('provider_datas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_temps', function (Blueprint $table)
        {
            $table->dropForeign(['provider_data_id']);
            $table->dropColumn('provider_data_id');
        });
    }
}
