<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxesPurchaseTemp extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
		'amount',
		'idDetailPurchaseTemp',
	];
}
