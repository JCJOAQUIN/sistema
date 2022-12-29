<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnProjectIdToBlueprintsrTable extends Migration
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
            $table->unsignedInteger('project_id')->nullable();
            $table->foreign('project_id')->references('idproyect')->on('projects');
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
            $table->dropForeign('blueprints_project_id_foreign');
            $table->dropColumn('project_id');
        });
    }
}
