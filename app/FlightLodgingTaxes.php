<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FlightLodgingTaxes extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
		'amount',
		'type',
		'flight_lodging_details_id',
	];
}
