<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseRecordRetention extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
		'amount',
		'idPurchaseRecord',
	];
}
