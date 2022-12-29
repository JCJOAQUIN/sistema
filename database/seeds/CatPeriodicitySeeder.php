<?php

use Illuminate\Database\Seeder;

class CatPeriodicitySeeder extends Seeder
{
	public function run()
	{
		$periodicities = [
			['c_periodicity' => '01', 'description' => 'Diario', 'days' => 1, 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['c_periodicity' => '02', 'description' => 'Semanal', 'days' => 7, 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['c_periodicity' => '03', 'description' => 'Catorcenal', 'days' => 14, 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['c_periodicity' => '04', 'description' => 'Quincenal', 'days' => 15, 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['c_periodicity' => '05', 'description' => 'Mensual', 'days' => 30, 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['c_periodicity' => '06', 'description' => 'Bimestral', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['c_periodicity' => '07', 'description' => 'Unidad obra', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['c_periodicity' => '08', 'description' => 'Comisión', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['c_periodicity' => '09', 'description' => 'Precio alzado', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['c_periodicity' => '10', 'description' => 'Decenal', 'validity_start' => '2017-01-19', 'validity_end' => NULL],
			['c_periodicity' => '99', 'description' => 'Otra Periodicidad', 'validity_start' => '2016-11-01', 'validity_end' => NULL]
		];

		foreach ($periodicities as $periodicity)
		{
			App\CatPeriodicity::create($periodicity);
		}
	}
}
