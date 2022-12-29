<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderSecondaryPriceTaxes extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
		'amount',
		'type',
		'providerSecondaryPrice_id',
	];
}
