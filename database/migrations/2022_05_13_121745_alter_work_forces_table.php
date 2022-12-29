<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWorkForcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_forces', function (Blueprint $table) {
            $table->string('location',300)->nullable()->after('wbs_id');
            $table->text('description')->nullable()->change();
            $table->text('work_force')->nullable()->change();
            $table->decimal('total_workers',20,2)->nullable()->change();
            $table->decimal('man_hours_per_day',20,2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_forces', function (Blueprint $table) {
            $table->dropColumn('location');
            $table->text('description')->nullable(false)->change();
            $table->text('work_force')->nullable(false)->change();
            $table->decimal('total_workers',20,2)->nullable(false)->change();
            $table->decimal('man_hours_per_day',20,2)->nullable(false)->change();
        });
    }
}
