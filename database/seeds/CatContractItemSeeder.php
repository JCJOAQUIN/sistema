<?php

use Illuminate\Database\Seeder;

class CatContractItemSeeder extends Seeder
{
	public function run()
	{
		$handle = fopen(__DIR__."/contract_items.json","r");
		while(($line = fgets($handle)) !== FALSE)
		{
			$discipline = json_decode($line, TRUE);
			App\CatContractItem::create($discipline);
		}
	}
}
