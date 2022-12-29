<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NullableRecommendationCommuniqueControlIncidents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('control_incidents', function (Blueprint $table) {
            $table->text('description')->change();
            $table->text('causes')->nullable()->change();
            $table->text('recommendation')->nullable()->change();
            $table->text('communique')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('control_incidents', function (Blueprint $table) {
            $table->string('description',300)->change();
            $table->string('causes',300)->nullable(false)->change();
            $table->string('recommendation',300)->nullable(false)->change();
            $table->string('communique',300)->nullable(false)->change();
        });
    }
}
