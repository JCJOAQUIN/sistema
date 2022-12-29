<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditTypeColumnActivityInCatContractItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_contract_items', function (Blueprint $table)
		{
			$table->text('activity')->nullable()->change();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cat_contract_items', function (Blueprint $table)
		{
			$table->string('activity')->nullable()->change();
		});
    }
}
