<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsToBoardroomReservationsTable extends Migration
{
    public function up()
    {
        Schema::table('boardroom_reservations', function (Blueprint $table) 
        {
            $table->datetime('start')->change();
            $table->datetime('end')->change();
        });
    }

    public function down()
    {
        Schema::table('boardroom_reservations', function (Blueprint $table) 
        {
            $table->date('start')->change();
            $table->date('end')->change();
        });
    }
}
