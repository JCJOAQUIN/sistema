<?php

use Illuminate\Database\Seeder;

class CatExport extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$exports = [
			['id' => '01', 'description' => 'No aplica', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['id' => '02', 'description' => 'Definitiva', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['id' => '03', 'description' => 'Temporal', 'validity_start' => '2022-01-01', 'validity_end' => NULL]
		];

		foreach ($exports as $ex)
		{
			App\CatExport::create($ex);
		}
	}
}
