<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToRequestHasReclassificationsTable extends Migration
{
    public function up()
    {
        Schema::table('request_has_reclassifications', function(Blueprint $table) 
        {
            $table->integer('code_wbs')->unsigned()->nullable();
            $table->integer('code_edt')->unsigned()->nullable();

            $table->foreign('code_wbs')->references('id')->on('cat_code_w_bs');
            $table->foreign('code_edt')->references('id')->on('cat_code_e_d_ts');

        });
    }

    public function down()
    {
        Schema::table('request_has_reclassifications', function(Blueprint $table) 
        {
            $table->dropForeign('code_wbs');
            $table->dropForeign('code_edt');

            $table->dropColumn('code_wbs');
            $table->dropColumn('code_edt');
        });
    }
}
