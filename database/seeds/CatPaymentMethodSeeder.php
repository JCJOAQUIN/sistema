<?php

use Illuminate\Database\Seeder;

class CatPaymentMethodSeeder extends Seeder
{
	public function run()
	{
		$paymentMethods = [
			['paymentMethod' => 'PPD', 'description' => 'Pago en parcialidades o diferido', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['paymentMethod' => 'PUE', 'description' => 'Pago en una sola exhibición', 'validity_start' => '2017-01-01', 'validity_end' => NULL]
		];

		foreach ($paymentMethods as $paymentMethod)
		{
			App\CatPaymentMethod::create($paymentMethod);
		}
	}
}
