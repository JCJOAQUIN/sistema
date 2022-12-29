<?php

use Illuminate\Database\Seeder;

class CatTaxesSeeder extends Seeder
{
	public function run()
	{
		$taxes = [
			['tax' => '001', 'description' => 'ISR', 'retention' => 'Si', 'transfer' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['tax' => '002', 'description' => 'IVA', 'retention' => 'Si', 'transfer' => 'Si', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['tax' => '003', 'description' => 'IEPS', 'retention' => 'Si', 'transfer' => 'Si', 'validity_start' => '2022-01-01', 'validity_end' => NULL]
		];

		foreach ($taxes as $tax)
		{
			App\CatTaxes::create($tax);
		}
	}
}
