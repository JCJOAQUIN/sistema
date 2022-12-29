<?php

use Illuminate\Database\Seeder;

class CatMachinerySeeder extends Seeder
{
    public function run()
    {
        $machineryEquipments = [
			['id' => '1', 'name' => 'Abocinador para tuberia'],
			['id' => '2', 'name' => 'Alineador exterior para tuberia varios diametros hasta 24"Ø'],
			['id' => '3', 'name' => 'Ampermetro de gancho, marca Fluke, mod. 36'],
			['id' => '4', 'name' => 'Andamios  estructurales metalicos tubulares (hasta 10 mts.)'],
			['id' => '5', 'name' => 'Biiceladora cortadora de lamina'],
			['id' => '6', 'name' => 'Biseladora-Cortadora varios diametros'],
			['id' => '7', 'name' => 'Bomba de achique de 1" de diam'],
			['id' => '8', 'name' => 'Bomba de achique de 2" de diam'],
			['id' => '9', 'name' => 'Bomba de achique de 3" de diam'],
			['id' => '10', 'name' => 'Bomba de achique de 4"x6" de diam'],
            ['id' => '11', 'name' => 'Bomba de embolo manual para pruebas'],
            ['id' => '12', 'name' => 'Bomba de llenado'],
            ['id' => '13', 'name' => 'Bomba de vacio (equipo de camara de vacio)'],
            ['id' => '14', 'name' => 'Bomba para alta presion, rango de 100 kg/cm2'],
            ['id' => '15', 'name' => 'Calibrador digital de espesores de tuberia y accesorios'],
            ['id' => '16', 'name' => 'Camara fotografica'],
            ['id' => '17', 'name' => 'Camion pipa para transporte de agua de 15,000 litros de capacidad'],
            ['id' => '18', 'name' => 'Camion plataforma c/Winche 7 Ton. de capacidad con malacate'],
            ['id' => '19', 'name' => 'Camion plataforma con grua hiab de 6/8 T,'],
            ['id' => '20', 'name' => 'Camion plataforma con redilas 10 ton. De capacidad'],
            ['id' => '21', 'name' => 'Camion titan grua'],
            ['id' => '22', 'name' => 'Camión Gondola 30 m3'],
            ['id' => '23', 'name' => 'Camion volteo 14 m3'],
            ['id' => '24', 'name' => 'Camion volteo 16 m3'],
            ['id' => '25', 'name' => 'Camion volteo 7 m3'],
		];

		foreach ($machineryEquipments as $me)
		{
			App\CatMachinery::create($me);
		}
    }
}