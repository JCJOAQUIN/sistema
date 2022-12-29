<?php

use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
	public function run()
	{
		$states = [
			['idstate' => 1, 'description' => 'Aguascalientes', 'c_state' => 'AGU', 'status' => 1],
			['idstate' => 2, 'description' => 'Baja California', 'c_state' => 'BCN', 'status' => 1],
			['idstate' => 3, 'description' => 'Baja California Sur', 'c_state' => 'BCS', 'status' => 1],
			['idstate' => 4, 'description' => 'Campeche', 'c_state' => 'CAM', 'status' => 1],
			['idstate' => 5, 'description' => 'Coahuila', 'c_state' => 'COA', 'status' => 1],
			['idstate' => 6, 'description' => 'Colima', 'c_state' => 'COL', 'status' => 1],
			['idstate' => 7, 'description' => 'Chiapas', 'c_state' => 'CHP', 'status' => 1],
			['idstate' => 8, 'description' => 'Chihuahua', 'c_state' => 'CHH', 'status' => 1],
			['idstate' => 9, 'description' => 'Ciudad de México', 'c_state' => 'CMX', 'status' => 1],
			['idstate' => 10, 'description' => 'Durango', 'c_state' => 'DUR', 'status' => 1],
			['idstate' => 11, 'description' => 'Guanajuato', 'c_state' => 'GUA', 'status' => 1],
			['idstate' => 12, 'description' => 'Guerrero', 'c_state' => 'GRO', 'status' => 1],
			['idstate' => 13, 'description' => 'Hidalgo', 'c_state' => 'HID', 'status' => 1],
			['idstate' => 14, 'description' => 'Jalisco', 'c_state' => 'JAL', 'status' => 1],
			['idstate' => 15, 'description' => 'Estado de México', 'c_state' => 'MEX', 'status' => 1],
			['idstate' => 16, 'description' => 'Michoacán', 'c_state' => 'MIC', 'status' => 1],
			['idstate' => 17, 'description' => 'Morelos', 'c_state' => 'MOR', 'status' => 1],
			['idstate' => 18, 'description' => 'Nayarit', 'c_state' => 'NAY', 'status' => 1],
			['idstate' => 19, 'description' => 'Nuevo León', 'c_state' => 'NLE', 'status' => 1],
			['idstate' => 20, 'description' => 'Oaxaca', 'c_state' => 'OAX', 'status' => 1],
			['idstate' => 21, 'description' => 'Puebla', 'c_state' => 'PUE', 'status' => 1],
			['idstate' => 22, 'description' => 'Querétaro', 'c_state' => 'QUE', 'status' => 1],
			['idstate' => 23, 'description' => 'Quintana Roo', 'c_state' => 'ROO', 'status' => 1],
			['idstate' => 24, 'description' => 'San Luis Potosí', 'c_state' => 'SLP', 'status' => 1],
			['idstate' => 25, 'description' => 'Sinaloa', 'c_state' => 'SIN', 'status' => 1],
			['idstate' => 26, 'description' => 'Sonora', 'c_state' => 'SON', 'status' => 1],
			['idstate' => 27, 'description' => 'Tabasco', 'c_state' => 'TAB', 'status' => 1],
			['idstate' => 28, 'description' => 'Tamaulipas', 'c_state' => 'TAM', 'status' => 1],
			['idstate' => 29, 'description' => 'Tlaxcala', 'c_state' => 'TLA', 'status' => 1],
			['idstate' => 30, 'description' => 'Veracruz', 'c_state' => 'VER', 'status' => 1],
			['idstate' => 31, 'description' => 'Yucatán', 'c_state' => 'YUC', 'status' => 1],
			['idstate' => 32, 'description' => 'Zacatecas', 'c_state' => 'ZAC', 'status' => 1],
			['idstate' => 33, 'description' => 'Distrito Federal', 'c_state' => 'DIF', 'status' => 0]
		];

		foreach ($states as $state)
		{
			App\State::create($state);
		}
	}
}
