<?php

use Illuminate\Database\Seeder;

class CatProdServSeeder extends Seeder
{
	public function run()
	{
		$handle = fopen(__DIR__."/prod_servs.json","r");
		while(($line = fgets($handle)) !== FALSE)
		{
			$prodServ = json_decode($line, TRUE);
			App\CatProdServ::create($prodServ);
		}
	}
}
