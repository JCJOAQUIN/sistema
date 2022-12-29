<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COSummaryConcept extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'clave',
		'concepto',
		'importe',
		'porcentaje',
	];
}
