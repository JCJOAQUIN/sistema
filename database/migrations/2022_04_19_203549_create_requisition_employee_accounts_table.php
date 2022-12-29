<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequisitionEmployeeAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requisition_employee_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('idEmployee')->unsigned()->nullable();
            $table->text('alias')->nullable();
            $table->string('clabe',100)->nullable();
            $table->string('account',100)->nullable();
            $table->string('cardNumber',100)->nullable();
            $table->string('branch',100)->nullable();
            $table->string('idCatBank',5)->nullable();
            $table->integer('recorder')->unsigned()->nullable();
            $table->text('beneficiary')->nullable();
            $table->tinyInteger('type')->nullable();
            $table->timestamps();

            $table->foreign('idEmployee')->references('id')->on('requisition_employees');
            $table->foreign('idCatBank')->references('c_bank')->on('cat_banks');
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
        Schema::dropIfExists('requisition_employee_accounts');
    }
}
