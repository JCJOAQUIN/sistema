<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COInterestsToConsiderDocument extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'idDocEmpresa',
		'negativos',
		'ambos',
		'tasaactiva',
		'tasapasiva',
	];
}
