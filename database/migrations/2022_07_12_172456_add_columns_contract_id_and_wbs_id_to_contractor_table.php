<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsContractIdAndWbsIdToContractorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contractors', function (Blueprint $table)
        {
            $table->unsignedInteger('contract_id')->nullable();
            $table->foreign('contract_id')->references('id')->on('contracts');
            $table->unsignedInteger('wbs_id')->nullable();
            $table->foreign('wbs_id')->references('id')->on('cat_code_w_bs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contractors', function (Blueprint $table)
        {
            $table->dropForeign('contractors_contract_id_foreign');
            $table->dropColumn('contract_id');
            $table->dropForeign('contractors_wbs_id_foreign');
            $table->dropColumn('wbs_id');
        });
    }
}
