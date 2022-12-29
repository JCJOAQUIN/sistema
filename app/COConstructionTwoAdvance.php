<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COConstructionTwoAdvance extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'numero',
		'anticipos',
		'porcentaje',
		'periododeentrega',
	];
}
