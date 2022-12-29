<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsContractIdAndWbsIdToBlueprintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blueprints', function (Blueprint $table)
        {
            $table->unsignedInteger('contract_id')->nullable();
            $table->foreign('contract_id')->references('id')->on('contracts');
            $table->dropForeign('blueprints_project_id_foreign');
            $table->dropColumn('project_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blueprints', function (Blueprint $table)
        {
            $table->dropForeign('blueprints_contract_id_foreign');
            $table->dropColumn('contract_id');
            $table->unsignedInteger('project_id')->nullable();
            $table->foreign('project_id')->references('idproyect')->on('projects');
        });
    }
}
