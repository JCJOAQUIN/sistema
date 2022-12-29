<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COSummaryGeneralIndirect extends Model
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
