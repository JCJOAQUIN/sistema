<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CODeterminationUtility extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'clave',
		'concepto',
		'formula',
		'importe',
		'porcentaje',
	];
}
