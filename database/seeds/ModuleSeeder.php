<?php

use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
	public function run()
	{
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');
		App\Module::truncate();
		$handle = fopen(__DIR__."/modules.json","r");
		while(($line = fgets($handle)) !== FALSE)
		{
			$module = json_decode($line, TRUE);
			App\Module::create($module);
		}
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}
}
