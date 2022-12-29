<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COGeneralTemplate extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'duraciondelaobra',
		'factor1',
		'factor2',
		'porcentaje',
	];
}
