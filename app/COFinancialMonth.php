<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COFinancialMonth extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'idConcept',
		'mes',
		'amount',
	];
}
