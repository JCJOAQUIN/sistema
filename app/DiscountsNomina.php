<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiscountsNomina extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'amount',
		'reason',
		'idnominaemployeenf',
	];
}
