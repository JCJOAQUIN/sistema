<?php

use Illuminate\Database\Seeder;

class AuditSubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subcategories = 
        [
            ['id' => '1', 'name' => 'A-1 Ajustan su equipo de protección personal', 'audit_category_id'=> '1'],
            ['id' => '2', 'name' => 'A-2 Cambian de posición', 'audit_category_id'=> '1'],
            ['id' => '3', 'name' => 'A-3 Reacomodan su trabajo', 'audit_category_id'=> '1'],
            ['id' => '4', 'name' => 'A-4 Dejan de trabajar', 'audit_category_id'=> '1'],
            ['id' => '5', 'name' => 'A-5 Colocan tierras', 'audit_category_id'=> '1'],
            ['id' => '6', 'name' => 'A-6 Colocan bloqueos', 'audit_category_id'=> '1'],
            ['id' => '7', 'name' => 'B-1 Cabeza', 'audit_category_id'=> '2'],
            ['id' => '8', 'name' => 'B-2 Ojos y cara', 'audit_category_id'=> '2'],
            ['id' => '9', 'name' => 'B-3 Oídos', 'audit_category_id'=> '2'],
            ['id' => '10', 'name' => 'B-4 Aparato respiratorio', 'audit_category_id'=> '2'],
            ['id' => '11', 'name' => 'B-5 Brazos y manos', 'audit_category_id'=> '2'],
            ['id' => '12', 'name' => 'B-6 Tronco', 'audit_category_id'=> '2'],
            ['id' => '13', 'name' => 'B-7 Piernas y pies', 'audit_category_id'=> '2'],
            ['id' => '14', 'name' => 'C-1 Golpear contra objetos', 'audit_category_id'=> '3'],
            ['id' => '15', 'name' => 'C-2 Golpeado por objetos', 'audit_category_id'=> '3'],
            ['id' => '16', 'name' => 'C-3 Atrapado entre, sobre o dentro de objetos', 'audit_category_id'=> '3'],
            ['id' => '17', 'name' => 'C-4 Caídas', 'audit_category_id'=> '3'],
            ['id' => '18', 'name' => 'C-5 Contacto con temperaturas extremas', 'audit_category_id'=> '3'],
            ['id' => '19', 'name' => 'C-6 Contacto con corriente eléctrica', 'audit_category_id'=> '3'],
            ['id' => '20', 'name' => 'C-7 Inhalación de materiales o substancias peligrosas', 'audit_category_id'=> '3'],
            ['id' => '21', 'name' => 'C-8 Absorción de materiales o substancias peligrosas', 'audit_category_id'=> '3'],
            ['id' => '22', 'name' => 'C-9 Ingestión de materiales o substancias peligrosas', 'audit_category_id'=> '3'],
            ['id' => '23', 'name' => 'C-10 Sobreesfuerzos', 'audit_category_id'=> '3'],
            ['id' => '24', 'name' => 'C-11 Movimientos repetitivos', 'audit_category_id'=> '3'],
            ['id' => '25', 'name' => 'C-12 Posiciones incómodas y posturas estáticas', 'audit_category_id'=> '3'],
            ['id' => '26', 'name' => 'D-1 Inadecuadas (os) para el trabajo', 'audit_category_id'=> '4'],
            ['id' => '27', 'name' => 'D-2 Uso incorrecto', 'audit_category_id'=> '4'],
            ['id' => '28', 'name' => 'D-3 Presentan condiciones inseguras', 'audit_category_id'=> '4'],
            ['id' => '29', 'name' => 'D-4 Falta de herramientas o equipos', 'audit_category_id'=> '4'],
            ['id' => '30', 'name' => 'E-1 Procedimientos no disponibles', 'audit_category_id'=> '5'],
            ['id' => '31', 'name' => 'E-2 Procedimientos con baja calidad', 'audit_category_id'=> '5'],
            ['id' => '32', 'name' => 'E-3 Procedimientos no comunicados ni entendidos', 'audit_category_id'=> '5'],
            ['id' => '33', 'name' => 'E-4 Procedimientos no aplicados en campo', 'audit_category_id'=> '5'],
            ['id' => '34', 'name' => 'E-5 Falta de verificación en campo', 'audit_category_id'=> '5'],
            ['id' => '35', 'name' => 'E-6 Falta de definición de estándares de O y L', 'audit_category_id'=> '5'],
            ['id' => '36', 'name' => 'E-7 Estándares de O y L inadecuados para el trabajo', 'audit_category_id'=> '5'],
            ['id' => '37', 'name' => 'E-8 Estándares de O y L no conocidos ni entendidos', 'audit_category_id'=> '5'],
            ['id' => '38', 'name' => 'E-9 Estándares de O y L no se cumplen', 'audit_category_id'=> '5']
        ];

        foreach($subcategories as $subcategory)
        {
            App\AuditSubcategory::create($subcategory);
        }
    }
}
