<?php

use Illuminate\Database\Seeder;

class CatDisciplineSeeder extends Seeder
{
	public function run()
	{
		$disciplines = [
			['id' => '1','indicator' => 'A','name' => 'Proceso'],
			['id' => '2','indicator' => 'B','name' => 'Topografía'],
			['id' => '3','indicator' => 'C','name' => 'Geotecnia'],
			['id' => '4','indicator' => 'D','name' => 'Arquitectura'],
			['id' => '5','indicator' => 'E','name' => 'Planificación - Control de Proyecto'],
			['id' => '6','indicator' => 'F','name' => 'Concreto - Grout'],
			['id' => '7','indicator' => 'G','name' => 'Estructuras Metálicas'],
			['id' => '8','indicator' => 'H','name' => 'Mecánica y Electromecánica'],
			['id' => '9','indicator' => 'J','name' => 'Instalaciones Hidráulicas Sanitarias'],
			['id' => '10','indicator' => 'K','name' => 'Tuberias - Interconexiones'],
			['id' => '11','indicator' => 'L','name' => 'Eléctrico - Red de Tierras'],
			['id' => '12','indicator' => 'M','name' => 'Telecomunicaciones'],
			['id' => '13','indicator' => 'N','name' => 'HVAC'],
			['id' => '14','indicator' => 'O','name' => 'Aseguramiento y Control de Calidad'],
			['id' => '15','indicator' => 'P','name' => 'Instrumentación'],
			['id' => '16','indicator' => 'Q','name' => 'Puentes'],
			['id' => '17','indicator' => 'R','name' => 'Sistema de Gas y Fuego'],
			['id' => '18','indicator' => 'S','name' => 'Seguridad, Salud y Medio Ambiente'],
			['id' => '19','indicator' => 'T','name' => 'Movimiento de Tierras'],
			['id' => '20','indicator' => 'U','name' => 'Tuberias Enterradas Pead'],
			['id' => '21','indicator' => 'V','name' => 'Tuberias Enterradas Acero'],
			['id' => '22','indicator' => 'W','name' => 'Modelado 3D'],
			['id' => '23','indicator' => 'X','name' => 'Flexibilidad'],
			['id' => '24','indicator' => 'Y','name' => 'Vía Ferreas'],
			['id' => '25','indicator' => 'Z','name' => 'General']
		];

		foreach ($disciplines as $d)
		{
			App\CatDiscipline::create($d);
		}
	}
}
