<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CostOverrunsNCGCustomers extends Model
{
	public $timestamps = false;
	protected $fillable = 
	[
		'idUpload',
		'nombrecliente',
		'area',
		'departamento',
		'direccioncliente',
		'coloniacliente',
		'codigopostalcliente',
		'ciudadcliente',
		'telefonocliente',
		'emailcliente',
		'contactocliente',
	];
}
