<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleFuel extends Model
{
	protected $fillable = 
	[
		'fuel_type',
		'tag',
		'date',
		'total',
		'vehicles_id',
		'users_id',
	];

	public function documents()
	{
		return $this->hasMany(VehicleDocument::class,'vehicles_fuel_id','id');
	}
}
