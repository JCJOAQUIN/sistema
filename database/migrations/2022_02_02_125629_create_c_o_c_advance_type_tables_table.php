<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCOCAdvanceTypeTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_o_c_advance_type_tables', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('idUpload')->unsigned();
            $table->decimal('costodirectodelaobra',24,6);
            $table->decimal('indirectodeobra',24,6);
            $table->decimal('costodirectoindirecto',24,6);
            $table->decimal('montototaldelaobra',24,6);
            $table->decimal('importeparafinanciamiento',24,6);
            $table->decimal('importeejercer1',24,6);
            $table->decimal('importeejercer2',24,6);
            $table->foreign('idUpload')->references('id')->on('cost_overruns');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('c_o_c_advance_type_tables');
    }
}
