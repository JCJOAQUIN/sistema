<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COFinancingCalcDocument extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'idDocEmpresa',
		'importetotal',
		'costodirectoindirecto',
	];
}
