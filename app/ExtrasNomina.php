<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtrasNomina extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'amount',
		'reason',
		'idnominaemployeenf',
	];
}
