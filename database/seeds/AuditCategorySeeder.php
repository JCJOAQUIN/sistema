<?php

use Illuminate\Database\Seeder;

class AuditCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['id' => '1', 'name' => 'A- REACCIONES DE LAS PERSONAS'],
            ['id' => '2', 'name' => 'B- EQUIPO DE PROTECCIÓN PERSONAL'],
            ['id' => '3', 'name' => 'C- POSICIONES DE LAS PERSONAS'],
            ['id' => '4', 'name' => 'D- HERRAMIENTAS Y EQUIPOS'],
            ['id' => '5', 'name' => 'E- PROCEDIMIENTOS, ORDEN Y LIMPIEZA']
        ];

        foreach($categories as $category)
        {
            App\AuditCategory::create($category);
        }
    }
}
