<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CODaysToPayDocument extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'idDocEmpresa',
		'dias',
	];
}
