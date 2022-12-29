<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bills', function (Blueprint $table)
		{
			$table->increments('idBill');
			$table->string('rfc',50);
			$table->text('businessName');
			$table->string('taxRegime',5)->nullable();
			$table->string('clientRfc',50)->nullable();
			$table->text('clientBusinessName')->nullable();
			$table->string('receiver_tax_regime',5)->nullable();
			$table->string('receiver_zip_code',50)->nullable();
			$table->string('uuid',50)->nullable();
			$table->string('noCertificate',50)->nullable();
			$table->string('satCertificateNo',50)->nullable();
			$table->datetime('expeditionDate')->nullable();
			$table->datetime('expeditionDateCFDI')->nullable();
			$table->datetime('stampDate')->nullable();
			$table->datetime('cancelRequestDate')->nullable();
			$table->datetime('CancelledDate')->nullable();
			$table->string('cancellation_reason',5)->nullable();
			$table->string('substitute_folio',50)->nullable();
			$table->string('postalCode',50)->nullable();
			$table->string('export',5)->nullable();
			$table->string('serie',100)->nullable();
			$table->string('folio',100)->nullable();
			$table->text('conditions')->nullable();
			$table->tinyInteger('status')->default(0)->comment('0. Pendiente de Timbrado 1. Pendiente de conciliación (Timbrado) 2. Conciliado (Timbrado) 3. En proceso de cancelación 4. Cancelado 5. En proceso de cancelación (temporal) 6. En cola para timbrado. 7. Error. 8. Pago editado y no se puede timbrar');
			$table->string('statusCFDI',500)->nullable();
			$table->string('statusCancelCFDI',500)->nullable();
			$table->decimal('subtotal',16,2)->nullable();
			$table->decimal('discount',16,2)->nullable();
			$table->decimal('tras',16,2)->nullable();
			$table->decimal('ret',16,2)->nullable();
			$table->decimal('total',16,2)->nullable();
			$table->string('related',5)->nullable();
			$table->text('originalChain')->nullable();
			$table->text('digitalStampCFDI')->nullable();
			$table->text('digitalStampSAT')->nullable();
			$table->text('signatureValueCancel')->nullable();
			$table->string('type',5)->nullable();
			$table->string('paymentMethod',5)->nullable();
			$table->string('paymentWay',5)->nullable();
			$table->string('currency',5)->nullable();
			$table->decimal('exchange',16,2)->nullable();
			$table->string('useBill',5)->nullable();
			$table->text('error')->nullable();
			$table->integer('folioRequest')->unsigned()->nullable();
			$table->tinyInteger('statusConciliation')->default(0);
			$table->integer('idProject')->unsigned()->nullable();
			$table->text('issuer_address')->nullable();
			$table->text('receiver_address')->nullable();
			$table->string('version',10)->default('3.3');
			$table->foreign('taxRegime')->references('taxRegime')->on('cat_tax_regimes');
			$table->foreign('receiver_tax_regime')->references('taxRegime')->on('cat_tax_regimes');
			$table->foreign('related')->references('typeRelation')->on('cat_relations');
			$table->foreign('type')->references('typeVoucher')->on('cat_type_bills');
			$table->foreign('paymentMethod')->references('paymentMethod')->on('cat_payment_methods');
			$table->foreign('paymentWay')->references('paymentWay')->on('cat_payment_ways');
			$table->foreign('currency')->references('currency')->on('cat_currencies');
			$table->foreign('useBill')->references('useVoucher')->on('cat_use_vouchers');
			$table->foreign('folioRequest')->references('folio')->on('request_models');
			$table->foreign('idProject')->references('idproyect')->on('projects');
			$table->foreign('export')->references('id')->on('cat_exports');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('bills');
	}
}
