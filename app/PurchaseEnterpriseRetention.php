<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseEnterpriseRetention extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idpurchaseEnterpriseRetention';
	protected $fillable   = 
	[
		'name',
		'amount',
		'idPurchaseEnterpriseDetail',
	];
}
