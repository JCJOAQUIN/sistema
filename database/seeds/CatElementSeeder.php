<?php

use Illuminate\Database\Seeder;

class CatElementSeeder extends Seeder
{
	public function run()
	{
		$elements = [
			['id' => 1, 'name' => 'Silla'],
			['id' => 2, 'name' => 'Mesa'],
			['id' => 3, 'name' => 'Proyector'],
			['id' => 4, 'name' => 'Pantalla'],
			['id' => 5, 'name' => 'Conexiones eléctricas'],
			['id' => 6, 'name' => 'Otros']
		];

		foreach ($elements as $element)
		{
			App\CatElements::create($element);
		}
	}
}
