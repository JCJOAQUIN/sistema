<?php

use Illuminate\Database\Seeder;

class CatPerceptionSeeder extends Seeder
{
	public function run()
	{
		$perceptions = [
			['id' => '001', 'description' => 'Sueldos, Salarios  Rayas y Jornales', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '002', 'description' => 'Gratificación Anual (Aguinaldo)', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '003', 'description' => 'Participación de los Trabajadores en las Utilidades PTU', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '004', 'description' => 'Reembolso de Gastos Médicos Dentales y Hospitalarios', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '005', 'description' => 'Fondo de Ahorro', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '006', 'description' => 'Caja de ahorro', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '009', 'description' => 'Contribuciones a Cargo del Trabajador Pagadas por el Patrón', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '010', 'description' => 'Premios por puntualidad', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '011', 'description' => 'Prima de Seguro de vida', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '012', 'description' => 'Seguro de Gastos Médicos Mayores', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '013', 'description' => 'Cuotas Sindicales Pagadas por el Patrón', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '014', 'description' => 'Subsidios por incapacidad', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '015', 'description' => 'Becas para trabajadores y/o hijos', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '019', 'description' => 'Horas extra', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '020', 'description' => 'Prima dominical', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '021', 'description' => 'Prima vacacional', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '022', 'description' => 'Prima por antigüedad', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '023', 'description' => 'Pagos por separación', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '024', 'description' => 'Seguro de retiro', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '025', 'description' => 'Indemnizaciones', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '026', 'description' => 'Reembolso por funeral', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '027', 'description' => 'Cuotas de seguridad social pagadas por el patrón', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '028', 'description' => 'Comisiones', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '029', 'description' => 'Vales de despensa', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '030', 'description' => 'Vales de restaurante', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '031', 'description' => 'Vales de gasolina', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '032', 'description' => 'Vales de ropa', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '033', 'description' => 'Ayuda para renta', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '034', 'description' => 'Ayuda para artículos escolares', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '035', 'description' => 'Ayuda para anteojos', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '036', 'description' => 'Ayuda para transporte', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '037', 'description' => 'Ayuda para gastos de funeral', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '038', 'description' => 'Otros ingresos por salarios', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '039', 'description' => 'Jubilaciones, pensiones o haberes de retiro', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '044', 'description' => 'Jubilaciones, pensiones o haberes de retiro en parcialidades', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '045', 'description' => 'Ingresos en acciones o títulos valor que representan bienes', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '046', 'description' => 'Ingresos asimilados a salarios', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '047', 'description' => 'Alimentación diferentes a los establecidos en el Art 94 último párrafo LISR', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '048', 'description' => 'Habitación', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '049', 'description' => 'Premios por asistencia', 'validity_start' => '2016-11-01', 'validity_end' => NULL],
			['id' => '050', 'description' => 'Viáticos', 'validity_start' => '2017-01-06', 'validity_end' => NULL],
			['id' => '051', 'description' => 'Pagos por gratificaciones, primas, compensaciones, recompensas u otros a extrabajadores derivados de jubilación en parcialidades', 'validity_start' => '2018-10-15', 'validity_end' => NULL],
			['id' => '052', 'description' => 'Pagos que se realicen a extrabajadores que obtengan una jubilación en parcialidades derivados de la ejecución de resoluciones judicial o de un laudo', 'validity_start' => '2018-10-15', 'validity_end' => NULL],
			['id' => '053', 'description' => 'Pagos que se realicen a extrabajadores que obtengan una jubilación en una sola exhibición derivados de la ejecución de resoluciones judicial o de un laudo', 'validity_start' => '2018-10-15', 'validity_end' => NULL]
		];

		foreach ($perceptions as $perception)
		{
			App\CatPerception::create($perception);
		}
	}
}
