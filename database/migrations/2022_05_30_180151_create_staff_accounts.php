<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_staff_employee')->unsigned()->nullable();
            $table->text('alias')->nullable();
            $table->string('clabe',100)->nullable();
            $table->string('account',100)->nullable();
            $table->string('cardNumber',100)->nullable();
            $table->string('branch',100)->nullable();
            $table->string('id_catbank',5)->nullable();
            $table->integer('recorder')->unsigned()->nullable();
            $table->text('beneficiary')->nullable();
            $table->tinyInteger('type')->nullable();
            $table->timestamps();

            $table->foreign('id_staff_employee')->references('id')->on('staff_employees');
            $table->foreign('id_catbank')->references('c_bank')->on('cat_banks');
            $table->foreign('recorder')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff_accounts');
    }
}
