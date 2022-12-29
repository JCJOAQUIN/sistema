<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteTableWBSContractors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('w_b_s_contractors');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('w_b_s_contractors', function (Blueprint $table)
		{
			$table->unsignedInteger('wbs_id');
			$table->unsignedInteger('contractor_id');
			$table->foreign('wbs_id')->references('id')->on('cat_code_w_bs');
			$table->foreign('contractor_id')->references('id')->on('contractors');
		});
    }
}
