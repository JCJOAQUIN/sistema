<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWBSContractTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('w_b_s_contract', function (Blueprint $table) {
            $table->unsignedInteger('wbs_id');
			$table->unsignedInteger('contract_id');
			$table->foreign('wbs_id')->references('id')->on('cat_code_w_bs');
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
        Schema::dropIfExists('w_b_s_contract');
    }
}
