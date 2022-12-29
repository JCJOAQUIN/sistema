<?php

use Illuminate\Database\Seeder;

class CatUnitySeeder extends Seeder
{
	public function run()
	{
		$handle = fopen(__DIR__."/unities.json","r");
		while(($line = fgets($handle)) !== FALSE)
		{
			$unity = json_decode($line, TRUE);
			App\CatUnity::create($unity);
		}
	}
}
