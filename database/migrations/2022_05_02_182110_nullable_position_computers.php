<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NullablePositionComputers extends Migration
{
    public function up()
    {
        Schema::table('computers', function (Blueprint $table)
		{
			$table->string('position',500)->nullable()->change();
		});
    }

    public function down()
    {
        Schema::table('computers', function (Blueprint $table)
        {
            $table->string('position',500)->change();
        });
    }
}
