<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RetentionPurchaseTemp extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
		'amount',
		'idDetailPurchaseTemp',
	];
}
