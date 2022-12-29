<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COCSecondAdvanceTypeTable extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'periodosprogramados',
		'periodofinaldecobro',
		'periododeamortizacion2doanticipo',
	];
}
