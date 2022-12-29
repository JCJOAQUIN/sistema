<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxesRefund extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idtaxesRefund';
	protected $fillable   = 
	[
		'name',
		'amount',
		'idRefundDetail',
	];
}
