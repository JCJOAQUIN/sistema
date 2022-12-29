<?php

use Illuminate\Database\Seeder;

class CatTypeHours extends Seeder
{
	public function run()
	{
		$hours = [
			['id' => '01', 'name' => 'Dobles'],
			['id' => '02', 'name' => 'Tripes'],
			['id' => '03', 'name' => 'Simples']
		];

		foreach ($hours as $hour)
		{
			App\CatTypeHour::create($hour);
		}
	}
}
