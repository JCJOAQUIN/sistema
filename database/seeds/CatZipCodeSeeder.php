<?php

use Illuminate\Database\Seeder;

class CatZipCodeSeeder extends Seeder
{
	public function run()
	{
		$handle = fopen(__DIR__."/zip_codes.json","r");
		while(($line = fgets($handle)) !== FALSE)
		{
			$code = json_decode($line, TRUE);
			App\CatZipCode::create($code);
		}
	}
}
