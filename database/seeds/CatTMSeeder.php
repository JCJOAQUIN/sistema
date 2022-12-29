<?php

use Illuminate\Database\Seeder;

class CatTMSeeder extends Seeder
{
	public function run()
	{
		$TMs = [
			['id' => '1', 'name' => 'Condición climatológica'],
			['id' => '2', 'name' => 'Falta de permisos'],
			['id' => '3', 'name' => 'Falta de material por el cliente'],
			['id' => '4', 'name' => 'Paros sindicales'],
			['id' => '5', 'name' => 'Otro']
		];

		foreach ($TMs as $tm)
		{
			App\CatTM::create($tm);
		}
	}
}
