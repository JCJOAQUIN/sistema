<?php

use Illuminate\Database\Seeder;

class CatPaymentWaySeeder extends Seeder
{
	public function run()
	{
		$paymentWays = [
			['paymentWay' => '01', 'description' => 'Efectivo', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '02', 'description' => 'Cheque nominativo', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '03', 'description' => 'Transferencia electrónica de fondos', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '04', 'description' => 'Tarjeta de crédito', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '05', 'description' => 'Monedero electrónico', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '06', 'description' => 'Dinero electrónico', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '08', 'description' => 'Vales de despensa', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '12', 'description' => 'Dación en pago', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '13', 'description' => 'Pago por subrogación', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '14', 'description' => 'Pago por consignación', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '15', 'description' => 'Condonación', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '17', 'description' => 'Compensación', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '23', 'description' => 'Novación', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '24', 'description' => 'Confusión', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '25', 'description' => 'Remisión de deuda', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '26', 'description' => 'Prescripción o caducidad', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '27', 'description' => 'A satisfacción del acreedor', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '28', 'description' => 'Tarjeta de débito', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '29', 'description' => 'Tarjeta de servicios', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '30', 'description' => 'Aplicación de anticipos', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '31', 'description' => 'Intermediario pagos', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['paymentWay' => '99', 'description' => 'Por definir', 'validity_start' => '2022-01-01', 'validity_end' => NULL]
		];

		foreach ($paymentWays as $paymentWay)
		{
			App\CatPaymentWay::create($paymentWay);
		}
	}
}
