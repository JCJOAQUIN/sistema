<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CostOverrunsNCGHeader extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'plazocalculado',
		'plazoreal',
		'decimalesredondeo',
		'primeramoneda',
		'segundamoneda',
		'remateprimeramoneda',
		'rematesegundamoneda',
	];
}
