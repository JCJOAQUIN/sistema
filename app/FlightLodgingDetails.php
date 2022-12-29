<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FlightLodgingDetails extends Model
{
	protected $fillable = 
	[
		'flight_lodging_id',
		'passenger_name',
		'born_date',
		'airline',
		'route',
		'departure_date',
		'departure_hour',
		'airline_back',
		'route_back',
		'departure_date_back',
		'departure_hour_back',
		'journey_description',
		'direct_superior',
		'last_family_journey_date',
		'checked_baggage',
		'hosting',
		'singin_date',
		'output_date',
		'tax',
		'total',
	];

	public function typeFlightData()
	{
		switch ($this->type_flight) 
		{
			case 1:
				return 'Sencillo';
				break;
			case 2:
				return 'Redondo';
				break;
		}
	}

	public function taxesData()
	{
		return $this->hasMany(FlightLodgingTaxes::class,'flight_lodging_details_id','id')->where('type',1);
	}

	public function retentionsData()
	{
		return $this->hasMany(FlightLodgingTaxes::class,'flight_lodging_details_id','id')->where('type',2);
	}
}
