<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RetentionBilling extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idretentionBilling';
	protected $fillable   = 
	[
		'name',
		'amount',
		'idbillingDetail',
	];
}
