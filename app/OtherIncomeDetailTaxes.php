<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OtherIncomeDetailTaxes extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'type',
		'description',
		'total',
		'idOtherIncomeDetail',
	];
}
