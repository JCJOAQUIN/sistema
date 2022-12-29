<?php

use Illuminate\Database\Seeder;

class CatIndustrialStaffSeeder extends Seeder
{
    public function run()
    {
        $industrialStaff = [
			['id' => '1', 'name' => 'Andamiero'],
			['id' => '2', 'name' => 'Auxiliar de Seguridad Industrial'],
			['id' => '3', 'name' => 'Ayudante de obra civil'],
			['id' => '4', 'name' => 'Ayudante de obra mecanica'],
			['id' => '5', 'name' => 'Ayudante de oficial'],
			['id' => '6', 'name' => 'Ayudante de andamiero'],
			['id' => '7', 'name' => 'Ayudante Electricista'],
			['id' => '8', 'name' => 'Ayudante General'],
			['id' => '9', 'name' => 'Banderero'],
			['id' => '10', 'name' => 'Bombero'],
            ['id' => '11', 'name' => 'Cabo de Obra'],
            ['id' => '12', 'name' => 'Cabo de obra de Instrumentacion y Control'],
            ['id' => '13', 'name' => 'Cabo de obra electromecanica'],
            ['id' => '14', 'name' => 'Cadenero'],
            ['id' => '15', 'name' => 'Checador de vehiculos'],
            ['id' => '16', 'name' => 'Chofer'],
            ['id' => '17', 'name' => 'Cortador de piso'],
            ['id' => '18', 'name' => 'Delegado sindical'],
            ['id' => '19', 'name' => 'Dibujante / Capturista'],
            ['id' => '20', 'name' => 'Electricista especialista'],
            ['id' => '21', 'name' => 'Especialista controlador de documentos'],
            ['id' => '22', 'name' => 'Especialista en comunicaciones'],
            ['id' => '23', 'name' => 'Ing calculista'],
            ['id' => '24', 'name' => 'Ing proyectista'],
            ['id' => '25', 'name' => 'Ingeniero especialista'],
		];

		foreach ($industrialStaff as $is)
		{
			App\CatIndustrialStaff::create($is);
		}
    }
}