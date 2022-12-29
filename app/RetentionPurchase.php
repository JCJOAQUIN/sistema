<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RetentionPurchase extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idretentionPurchase';
	protected $fillable   = 
	[
		'name',
		'amount',
		'idDetailPurchase',
	];
}
