<?php

use Illuminate\Database\Seeder;

class CatRegimeTypeSeeder extends Seeder
{
	public function run()
	{
		$regimes = [
			['id' => '02', 'description' => 'Sueldos (Incluye ingresos señalados en la fracción I del artículo 94 de LISR)', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => '03', 'description' => 'Jubilados', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => '04', 'description' => 'Pensionados', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => '05', 'description' => 'Asimilados Miembros Sociedades Cooperativas Produccion', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => '06', 'description' => 'Asimilados Integrantes Sociedades Asociaciones Civiles', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => '07', 'description' => 'Asimilados Miembros consejos', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => '08', 'description' => 'Asimilados comisionistas', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => '09', 'description' => 'Asimilados Honorarios', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => '10', 'description' => 'Asimilados acciones', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => '11', 'description' => 'Asimilados otros', 'validity_start' => '2017-01-01', 'validity_end' => NULL],
			['id' => '12', 'description' => 'Jubilados o Pensionados', 'validity_start' => '2017-03-27', 'validity_end' => NULL],
			['id' => '13', 'description' => 'Indemnización o Separación', 'validity_start' => '2018-10-15', 'validity_end' => NULL],
			['id' => '99', 'description' => 'Otro Regimen', 'validity_start' => '2017-01-01', 'validity_end' => NULL]
		];

		foreach ($regimes as $regime)
		{
			App\CatRegimeType::create($regime);
		}
	}
}
