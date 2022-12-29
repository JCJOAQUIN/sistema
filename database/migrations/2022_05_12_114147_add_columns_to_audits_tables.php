<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToAuditsTables extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('audits', function (Blueprint $table) 
        {
            $table->decimal('ias',16,2)->nullable();
            $table->integer('severity_factor')->nullable();
            $table->decimal('iai',16,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('audits', function (Blueprint $table) 
        {
            $table->dropColumn('ias');
            $table->dropColumn('severity_factor');
            $table->dropColumn('iai');
        });
    }
}
