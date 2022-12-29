<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
	protected $fillable = 
	[
		'property',
		'location',
		'type_property',
		'use_property',
		'number_of_rooms',
		'number_of_bathrooms',
		'parking_lot',
		'kitchen_room',
		'garden',
		'boardroom',
		'furnished',
		'measurements',
		'users_id',
	];

	public function payments()
	{
		return $this->hasMany(PropertyPayments::class,'property_id','id');
	}

	public function legalDocuments()
	{
		return $this->hasMany(PropertyLegalDocuments::class,'property_id','id');
	}
}
