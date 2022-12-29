<?php

use Illuminate\Database\Seeder;

class CatRelationSeeder extends Seeder
{
	public function run()
	{
		$relations = [
			['typeRelation' => '01', 'description' => 'Nota de crédito de los documentos relacionados', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['typeRelation' => '02', 'description' => 'Nota de débito de los documentos relacionados', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['typeRelation' => '03', 'description' => 'Devolución de mercancía sobre facturas o traslados previos', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['typeRelation' => '04', 'description' => 'Sustitución de los CFDI previos', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['typeRelation' => '05', 'description' => 'Traslados de mercancías facturados previamente', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['typeRelation' => '06', 'description' => 'Factura generada por los traslados previos', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['typeRelation' => '07', 'description' => 'CFDI por aplicación de anticipo', 'validity_start' => '2022-01-01', 'validity_end' => NULL, 'cfdi_3_3' => 1, 'cfdi_4_0' => 1],
			['typeRelation' => '08', 'description' => 'Factura generada por pagos en parcialidades', 'validity_start' => '2017-12-05', 'validity_end' => '2022-04-30', 'cfdi_3_3' => 1, 'cfdi_4_0' => 0],
			['typeRelation' => '09', 'description' => 'Factura generada por pagos diferidos', 'validity_start' => '2017-12-05', 'validity_end' => '2022-04-30', 'cfdi_3_3' => 1, 'cfdi_4_0' => 0]
		];

		foreach ($relations as $relation)
		{
			App\CatRelation::create($relation);
		}
	}
}
