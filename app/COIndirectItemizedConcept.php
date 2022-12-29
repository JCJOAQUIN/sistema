<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COIndirectItemizedConcept extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'type',
		'concepto',
		'monto1',
		'porcentaje1',
		'monto2',
		'porcentaje2',
	];
}
