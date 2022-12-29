<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffEmployee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_employees', function (Blueprint $table)
		{
            $table->increments('id');
            $table->string('name',500)->nullable();
            $table->string('last_name',500)->nullable();
            $table->string('scnd_last_name',500)->nullable();
            $table->string('curp',20)->nullable();
            $table->string('rfc',15)->nullable();
            $table->string('tax_regime',5)->nullable();
            $table->string('imss',15)->nullable();
            $table->text('street')->nullable();
            $table->string('number',500)->nullable();
            $table->text('colony')->nullable();
            $table->string('cp',20)->nullable();
            $table->text('city')->nullable();
            $table->decimal('workedDays',10,1)->nullable();
            $table->decimal('salaryFiscal',20,2)->nullable();
            $table->decimal('salaryNoFiscal',20,2)->nullable();
            $table->integer('state_id')->unsigned()->nullable();
            $table->string('email',500)->nullable();

            $table->integer('state')->unsigned()->nullable();
            $table->integer('project')->unsigned()->nullable();
            $table->integer('enterprise')->unsigned()->nullable();
            $table->integer('account')->unsigned()->nullable();
            $table->integer('direction')->unsigned()->nullable();
            $table->integer('department')->unsigned()->nullable();
            $table->text('position')->nullable();
            $table->date('admissionDate')->nullable();
            $table->date('imssDate')->nullable();
            $table->date('downDate')->nullable();
            $table->date('endingDate')->nullable();
            $table->date('reentryDate')->nullable();
            $table->string('workerType',5)->nullable();
            $table->string('regime_id',5)->nullable();
            $table->tinyInteger('workerStatus')->nullable();
            $table->text('status_reason')->nullable();
            $table->decimal('sdi',16,2)->nullable();
            $table->string('periodicity',3)->nullable();
            $table->string('employer_register',100)->nullable();
            $table->integer('paymentWay')->unsigned()->nullable();
            $table->decimal('netIncome',16,2)->nullable();
            $table->decimal('complement',16,2)->nullable();
            $table->decimal('fonacot',16,2)->nullable();
            $table->integer('nomina')->nullable();
            $table->integer('bono')->nullable();
            $table->string('infonavitCredit',100)->nullable();
            $table->decimal('infonavitDiscount',24,6)->nullable();
            $table->tinyInteger('infonavitDiscountType')->nullable();
            $table->tinyInteger('alimonyDiscountType')->nullable();
            $table->decimal('alimonyDiscount',24,6)->nullable();
            $table->tinyInteger('status_imss')->default(1)->nullable();
            $table->integer('wbs_id')->unsigned()->nullable();
            $table->string('immediate_boss',500)->nullable();
            $table->string('position_immediate_boss',500)->nullable();
            $table->decimal('viatics',16,2)->nullable();
            $table->decimal('camping',16,2)->nullable();
            $table->string('replace',500)->nullable();
            $table->text('purpose')->nullable();
            $table->text('requeriments')->nullable();
            $table->text('observations')->nullable();
            $table->integer('subdepartment_id')->unsigned()->nullable();
            $table->string('doc_birth_certificate',250)->nullable();
            $table->string('doc_proof_of_address',250)->nullable();
            $table->string('doc_nss',250)->nullable();
            $table->string('doc_ine',250)->nullable();
            $table->string('doc_curp',250)->nullable();
            $table->string('doc_rfc',250)->nullable();
            $table->string('doc_cv',250)->nullable();
            $table->string('doc_proof_of_studies',250)->nullable();
            $table->string('doc_professional_license',250)->nullable();
            $table->string('doc_requisition',250)->nullable();
            $table->integer('computer_required')->nullable();
            $table->tinyInteger('qualified_employee')->nullable();
            $table->tinyInteger('version')->default(0)->nullable();
            $table->integer('staff_id')->unsigned()->nullable();
            $table->timestamps();


            $table->foreign('state_id')->references('idstate')->on('states');
            $table->foreign('tax_regime')->references('taxRegime')->on('cat_tax_regimes');
            $table->foreign('state')->references('idstate')->on('states');
            $table->foreign('project')->references('idproyect')->on('projects');
            $table->foreign('enterprise')->references('id')->on('enterprises');
            $table->foreign('account')->references('idAccAcc')->on('accounts');
            $table->foreign('direction')->references('id')->on('areas');
            $table->foreign('department')->references('id')->on('departments');
            $table->foreign('workerType')->references('id')->on('cat_contract_types');
            $table->foreign('regime_id')->references('id')->on('cat_regime_types');
            $table->foreign('periodicity')->references('c_periodicity')->on('cat_periodicities');
            $table->foreign('paymentWay')->references('idPaymentMethod')->on('payment_methods');
            $table->foreign('wbs_id')->references('id')->on('cat_code_w_bs');
            $table->foreign('subdepartment_id')->references('id')->on('subdepartments');
            $table->foreign('staff_id')->references('idStaff')->on('staff');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff_employees');
    }
}
