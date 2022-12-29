<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CODaysToConsiderDocument extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'idDocEmpresa',
		'anofiscal',
		'anocomercial',
	];
}
