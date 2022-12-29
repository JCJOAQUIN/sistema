<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PropertyPayments extends Model
{
	protected $fillable = 
	[
		'payment_type',
		'periodicity',
		'date_range',
		'amount',
		'path',
		'property_id',
		'user_id',
	];
}
