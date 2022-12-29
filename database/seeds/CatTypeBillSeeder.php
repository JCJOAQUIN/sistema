<?php

use Illuminate\Database\Seeder;

class CatTypeBillSeeder extends Seeder
{
	public function run()
	{
		$types = [
			['typeVoucher' => 'I', 'description' => 'Ingreso', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['typeVoucher' => 'E', 'description' => 'Egreso', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['typeVoucher' => 'T', 'description' => 'Traslado', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['typeVoucher' => 'N', 'description' => 'Nómina', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['typeVoucher' => 'P', 'description' => 'Pago', 'validity_start' => '2022-01-01', 'validity_end' => NULL]
		];

		foreach ($types as $type)
		{
			App\CatTypeBill::create($type);
		}
	}
}
