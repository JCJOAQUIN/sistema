<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COConstructionAMIP extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'anticipo1',
		'anticipo2',
		'monto1',
		'monto2',
		'importe1',
		'importe2',
		'periodo',
	];
}
