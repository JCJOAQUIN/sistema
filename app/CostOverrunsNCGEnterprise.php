<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CostOverrunsNCGEnterprise extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'razonsocial',
		'domicilio',
		'colonia',
		'ciudad',
		'estado',
		'rfc',
		'telefono',
		'email1',
		'email2',
		'email3',
		'cmic',
		'infonavit',
		'imss',
		'responsable',
		'cargo',
	];
}
