<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COConstructionBudgetExceed extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'numero',
		'anticipos',
		'porcentaje',
		'importeaejercer',
		'importedeanticipo',
		'periododeentrega',
	];
}
