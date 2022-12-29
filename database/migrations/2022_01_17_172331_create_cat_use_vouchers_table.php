<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatUseVouchersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cat_use_vouchers', function (Blueprint $table)
		{
			$table->string('useVoucher',5)->primary();
			$table->string('description',500);
			$table->string('physical',5);
			$table->string('moral',5);
			$table->date('validity_start')->nullable();
			$table->date('validity_end')->nullable();
			$table->text('tax_regime_receptor')->nullable();
			$table->tinyInteger('cfdi_3_3');
			$table->tinyInteger('cfdi_4_0');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('cat_use_vouchers');
	}
}
