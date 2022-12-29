<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COPeriodProgram extends Model
{
	public $timestamps = false;
	protected $fillable = 
	[
		'idUpload',
		'programado',
		'titulo',
		'diasnaturales',
		'diastotales',
		'factorano',
		'ano',
		'importedelperiodo',
	];
}
