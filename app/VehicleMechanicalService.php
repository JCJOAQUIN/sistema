<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleMechanicalService extends Model
{
	protected $fillable =
	[
		'date_last_service',
		'next_service_date',
		'repairs',
		'total',
		'vehicles_id',
		'users_id',
	];

	public function documents()
	{
		return $this->hasMany(VehicleDocument::class,'vehicles_mechanical_services_id','id');
	}
}
