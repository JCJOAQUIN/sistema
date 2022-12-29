<?php

use Illuminate\Database\Seeder;

class CatWeatherConditionsSeeder extends Seeder
{
	public function run()
	{
		$weatherConditions = [
			['id' => '1', 'name' => 'Llovizna'],
			['id' => '2', 'name' => 'Lluvia Esporádica'],
			['id' => '3', 'name' => 'Lluvia Fuerte'],
			['id' => '4', 'name' => 'Lluvia Moderada'],
			['id' => '5', 'name' => 'Medio Nublado'],
			['id' => '6', 'name' => 'Nublado'],
			['id' => '7', 'name' => 'Soleado - Despejado'],
			['id' => '8', 'name' => 'Viento Fuerte'],
			['id' => '9', 'name' => 'Viento Ligero'],
			['id' => '10', 'name' => 'Viento Moderado']
		];

		foreach ($weatherConditions as $wc)
		{
			App\CatWeatherConditions::create($wc);
		}
	}
}
