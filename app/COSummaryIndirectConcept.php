<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COSummaryIndirectConcept extends Model
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
		'montototal',
		'porcentajetotal',
	];
}
