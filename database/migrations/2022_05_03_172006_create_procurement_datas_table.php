<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcurementDatasTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('procurement_datas', function (Blueprint $table)
		{
			$table->increments('id');
			$table->text('wbs')->nullable();
			$table->text('nombre_wbs')->nullable();
			$table->text('no_rq')->nullable();
			$table->text('fecha_recepcion_rq')->nullable();
			$table->text('comprador')->nullable();
			$table->text('estatus')->nullable();
			$table->text('pendiente_documental')->nullable();
			$table->text('fecha_oc')->nullable();
			$table->text('pedido_oc')->nullable();
			$table->text('descripcion_pedido')->nullable();
			$table->text('proveedor')->nullable();
			$table->text('partida')->nullable();
			$table->text('tag')->nullable();
			$table->text('pulgadas_mat')->nullable();
			$table->text('unidad')->nullable();
			$table->text('cantidad')->nullable();
			$table->text('concepto')->nullable();
			$table->text('moneda')->nullable();
			$table->text('precio_unitario_mxn')->nullable();
			$table->text('importe_mxn')->nullable();
			$table->text('iva_16')->nullable();
			$table->text('total_partida_mxn')->nullable();
			$table->text('total_oc_mxn')->nullable();
			$table->text('precio_unitario_usd')->nullable();
			$table->text('importe_usd')->nullable();
			$table->text('iva_16_2')->nullable();
			$table->text('total_partida_usd')->nullable();
			$table->text('total_oc_usd')->nullable();
			$table->text('incoterm')->nullable();
			$table->text('hitos')->nullable();
			$table->text('hito_descrtipcion')->nullable();
			$table->text('factura')->nullable();
			$table->text('fecha')->nullable();
			$table->text('monto_con_iva')->nullable();
			$table->text('total_facturado')->nullable();
			$table->text('fecha_pago')->nullable();
			$table->text('est')->nullable();
			$table->text('fianza')->nullable();
			$table->text('estatus_fianza')->nullable();
			$table->text('entrega_contractual')->nullable();
			$table->text('recepcion_en_sitio')->nullable();
			$table->text('observaciones')->nullable();
			$table->unsignedInteger('user_id');
			$table->timestamps();
			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('procurement_datas');
	}
}
