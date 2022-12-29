<?php

use Illuminate\Database\Seeder;

class CatTaxRegimeSeeder extends Seeder
{
	public function run()
	{
		$taxRegimes = [
			['taxRegime' => '601', 'description' => 'General de Ley Personas Morales', 'physical' => 'No', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '603', 'description' => 'Personas Morales con Fines no Lucrativos', 'physical' => 'No', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '605', 'description' => 'Sueldos y Salarios e Ingresos Asimilados a Salarios', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '606', 'description' => 'Arrendamiento', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '607', 'description' => 'Régimen de Enajenación o Adquisición de Bienes', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '608', 'description' => 'Demás ingresos', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '610', 'description' => 'Residentes en el Extranjero sin Establecimiento Permanente en México', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '611', 'description' => 'Ingresos por Dividendos (socios y accionistas)', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '612', 'description' => 'Personas Físicas con Actividades Empresariales y Profesionales', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '614', 'description' => 'Ingresos por intereses', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '615', 'description' => 'Régimen de los ingresos por obtención de premios', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '616', 'description' => 'Sin obligaciones fiscales', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '620', 'description' => 'Sociedades Cooperativas de Producción que optan por diferir sus ingresos', 'physical' => 'No', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '621', 'description' => 'Incorporación Fiscal', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '622', 'description' => 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras', 'physical' => 'No', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '623', 'description' => 'Opcional para Grupos de Sociedades', 'physical' => 'No', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '624', 'description' => 'Coordinados', 'physical' => 'No', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '625', 'description' => 'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas', 'physical' => 'Sí', 'moral' => 'No', 'validity_start' => '2022-01-01', 'validity_end' => NULL],
			['taxRegime' => '626', 'description' => 'Régimen Simplificado de Confianza', 'physical' => 'Sí', 'moral' => 'Sí', 'validity_start' => '2022-01-01', 'validity_end' => NULL]
		];

		foreach ($taxRegimes as $taxRegime)
		{
			App\CatTaxRegime::create($taxRegime);
		}
	}
}
