<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxesPurchase extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idtaxesPurchase';
	protected $fillable   = 
	[
		'name',
		'amount',
		'idDetailPurchase',
	];
}
