<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIdnofbillsOnConciliationMovement extends Migration
{
    public function up()
    {
        Schema::table('conciliation_movement_bills', function(Blueprint $table) 
		{
            $table->integer('idNoFiscalBill')->unsigned()->nullable()->after('idbill');;
            $table->foreign('idNoFiscalBill')->references('idBill')->on('non_fiscal_bills');
		});
    }

    public function down()
    {
        Schema::table('conciliation_movement_bills', function(Blueprint $table) 
		{
            $table->dropForeign(['idNoFiscalBill']);
            $table->dropColumn('idNoFiscalBill');
		});   
    }
}
