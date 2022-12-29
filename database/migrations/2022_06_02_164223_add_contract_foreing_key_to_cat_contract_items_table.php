<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractForeingKeyToCatContractItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_contract_items', function (Blueprint $table) {
            $table->unsignedInteger('contract_id')->nullable();
            $table->foreign('contract_id')->references('id')->on('contracts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cat_contract_items', function (Blueprint $table) {
            $table->dropForeign('cat_contract_items_contract_id_foreign');
            $table->dropColumn('contract_id');
        });
    }
}
