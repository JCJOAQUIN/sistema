<?php

use Illuminate\Database\Seeder;

class CatTypeDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cat_type_documents = [
            ['id' => '1', 'name' => 'Especificaciones Técnicas'],
            ['id' => '2', 'name' => 'Propietario'],
            ['id' => '3', 'name' => 'Combustible'],
            ['id' => '4', 'name' => 'Impuestos'],
            ['id' => '5', 'name' => 'Multas'],
            ['id' => '6', 'name' => 'Seguro'],
            ['id' => '7', 'name' => 'Servicios Mecánicos'],
        ];

        foreach ($cat_type_documents as $document)
        {
            App\CatTypeDocument::create($document);
        }
    }
}
