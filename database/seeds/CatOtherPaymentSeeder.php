<?php

use Illuminate\Database\Seeder;

class CatOtherPaymentSeeder extends Seeder
{
	public function run()
	{
		$otherPayments = [
			['id' => '001', 'description' => 'Reintegro de ISR pagado en exceso (siempre que no haya sido enterado al SAT).', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => '002', 'description' => 'Subsidio para el empleo (efectivamente entregado al trabajador).', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => '003', 'description' => 'Viáticos (entregados al trabajador).', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => '004', 'description' => 'Aplicación de saldo a favor por compensación anual.', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => '005', 'description' => 'Reintegro de ISR retenido en exceso de ejercicio anterior (siempre que no haya sido enterado al SAT).', 'validity_start' => '2017-12-05', 'validity_end' => NULL],
			['id' => '006', 'description' => 'Alimentos en bienes (Servicios de comedor y comida) Art 94 último párrafo LISR.', 'validity_start' => '2020-01-01', 'validity_end' => NULL],
			['id' => '007', 'description' => 'ISR ajustado por subsidio.', 'validity_start' => '2020-01-01', 'validity_end' => NULL],
			['id' => '008', 'description' => 'Subsidio efectivamente entregado que no correspondía (Aplica sólo cuando haya ajuste al cierre de mes en relación con el Apéndice 7 de la guía de llenado de nómina).', 'validity_start' => '2020-01-01', 'validity_end' => NULL],
			['id' => '009', 'description' => 'Reembolso de descuentos efectuados para el crédito de vivienda.', 'validity_start' => '2020-04-20', 'validity_end' => NULL],
			['id' => '999', 'description' => 'Pagos distintos a los listados y que no deben considerarse como ingreso por sueldos, salarios o ingresos asimilados.', 'validity_start' => '2017-01-01', 'validity_end' => NULL]
		];

		foreach ($otherPayments as $otherPayment)
		{
			App\CatOtherPayment::create($otherPayment);
		}
	}
}
