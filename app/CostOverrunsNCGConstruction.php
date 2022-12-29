<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CostOverrunsNCGConstruction extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'nombredelaobra',
		'direcciondelaobra',
		'coloniadelaobra',
		'ciudaddelaobra',
		'estadodelaobra',
		'codigopostaldelaobra',
		'telefonodelaobra',
		'emaildelaobra',
		'responsabledelaobra',
		'cargoresponsabledelaobra',
		'fechainicio',
		'fechaterminacion',
		'totalpresupuestoprimeramoneda',
		'totalpresupuestosegundamoneda',
		'porcentajeivapresupuesto',
	];
}
