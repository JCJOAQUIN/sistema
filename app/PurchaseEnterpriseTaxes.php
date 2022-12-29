<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseEnterpriseTaxes extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idpurchaseEnterpriseTaxes';
	protected $fillable   = 
	[
		'name',
		'amount',
		'idPurchaseEnterpriseDetail',
	];
}
