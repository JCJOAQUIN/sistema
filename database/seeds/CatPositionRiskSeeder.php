<?php

use Illuminate\Database\Seeder;

class CatPositionRiskSeeder extends Seeder
{
	public function run()
	{
		$positions = [
			['id' => 1, 'description' => 'Clase I', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => 2, 'description' => 'Clase II', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => 3, 'description' => 'Clase III', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => 4, 'description' => 'Clase IV', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => 5, 'description' => 'Clase V', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => 99, 'description' => 'No aplica', 'validity_start' => '2017-08-13', 'validity_end' => NULL]
		];

		foreach ($positions as $position)
		{
			App\CatPositionRisk::create($position);
		}
	}
}
