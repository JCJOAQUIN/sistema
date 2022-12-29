<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COIndirectItemizedGeneral extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'montoobra',
		'totales',
		'indirecto',
	];
}
