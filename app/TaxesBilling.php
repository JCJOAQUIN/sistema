<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxesBilling extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idtaxesBilling';
	protected $fillable   = 
	[
		'name',
		'amount',
		'idbillingDetail',
	];
}
