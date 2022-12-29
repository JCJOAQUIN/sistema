<?php

use Illuminate\Database\Seeder;

class CatTaxObject extends Seeder
{
	public function run()
	{
		$taxObjects = [
			['id' => '01', 'description' => 'No objeto de impuesto', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['id' => '02', 'description' => 'Sí objeto de impuesto', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['id' => '03', 'description' => 'Sí objeto del impuesto y no obligado al desglose', 'validity_start' => '2022-01-01', 'validity_end' => NULL]
		];

		foreach ($taxObjects as $taxObject)
		{
			App\CatTaxObject::create($taxObject);
		}
	}
}
