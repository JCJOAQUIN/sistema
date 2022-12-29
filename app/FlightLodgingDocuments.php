<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FlightLodgingDocuments extends Model
{
	const CREATED_AT = 'date';
	protected $fillable = 
	[
		'name',
		'path',
		'users_id',
		'flight_lodging_id',
	];

	public function userData()
	{
		return $this->hasOne(User::class,'id','users_id');
	}
}
