<?php

use Illuminate\Database\Seeder;

class CatContractTypeSeeder extends Seeder
{
	public function run()
	{
		$contracts = [
			['id' => '01', 'description' => 'Contrato de trabajo por tiempo indeterminado'],
			['id' => '02', 'description' => 'Contrato de trabajo para obra determinada'],
			['id' => '03', 'description' => 'Contrato de trabajo por tiempo determinado'],
			['id' => '04', 'description' => 'Contrato de trabajo por temporada'],
			['id' => '05', 'description' => 'Contrato de trabajo sujeto a prueba'],
			['id' => '06', 'description' => 'Contrato de trabajo con capacitación inicial'],
			['id' => '07', 'description' => 'Modalidad de contratación por pago de hora laborada'],
			['id' => '08', 'description' => 'Modalidad de trabajo por comisión laboral'],
			['id' => '09', 'description' => 'Modalidades de contratación donde no existe relación de trabajo'],
			['id' => '10', 'description' => 'Jubilación, pensión, retiro.'],
			['id' => '99', 'description' => 'Otro contrato']
		];

		foreach($contracts as $contract)
		{
			App\CatContractType::create($contract);
		}
	}
}
