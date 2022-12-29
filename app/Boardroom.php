<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Boardroom extends Model
{
	protected $fillable = 
	[
		'name',
		'description',
		'location',
		'enterprise_id',
		'max_capacity',
		'property_id'
	];

	public function elements()
	{
		return $this->hasMany(BoardroomElements::class,'boardroom_id','id');
	}
	
	public function reservations()
	{
		return $this->hasMany(BoardroomReservations::class,'boardroom_id','id');
	}

	public function enterprise()
	{
		return $this->hasOne(Enterprise::class,'id','enterprise_id');
	}

	public function locationData()
	{
		return $this->hasOne(Property::class,'id','property_id');
	}
}
