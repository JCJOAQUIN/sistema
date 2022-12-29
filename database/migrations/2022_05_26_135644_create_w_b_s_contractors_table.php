<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWBSContractorsTable extends Migration
{
	public function up()
	{
		Schema::create('w_b_s_contractors', function (Blueprint $table)
		{
			$table->unsignedInteger('wbs_id');
			$table->unsignedInteger('contractor_id');
			$table->foreign('wbs_id')->references('id')->on('cat_code_w_bs');
			$table->foreign('contractor_id')->references('id')->on('contractors');
		});
	}

	public function down()
	{
		Schema::dropIfExists('w_b_s_contractors');
	}
}
