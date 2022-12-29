<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COAdditionalChargeCalcDocument extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'idDocEmpresa',
		'sobreelimporte',
		'costodirecto',
	];
}
