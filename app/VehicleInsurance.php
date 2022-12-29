<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleInsurance extends Model
{
	protected $fillable =
	[
		'insurance_carrier',
		'expiration_date',
		'total',
		'vehicles_id',
		'users_id',
	];

	public function documents()
	{
		return $this->hasMany(VehicleDocument::class,'vehicles_insurances_id','id');
	}
}
