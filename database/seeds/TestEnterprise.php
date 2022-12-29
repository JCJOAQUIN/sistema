<?php

use Illuminate\Database\Seeder;

class TestEnterprise extends Seeder
{
	public function run()
	{
		$enterprise = [
			'name' => 'ESCUELA KEMPER URGATE SA DE CV',
			'rfc' => 'EKU9003173C9',
			'details' => 'ESCUELA KEMPER URGATE SA DE CV',
			'address' => 'Ejemplo',
			'number' => '1',
			'colony' => 'Ejemplo',
			'postalCode' => '51000',
			'city' => 'Ciudad de México',
			'phone' => '0000000000',
			'state_idstate' => 9,
			'path' => 'AdG1646850263_enterprise.png',
			'taxRegime' => '601',
			'status' => 'ACTIVE',
			'noCertificado' => '30001000000400002434',
			'folioBill' => 0,
		];
		App\Enterprise::create($enterprise);
	}
}
