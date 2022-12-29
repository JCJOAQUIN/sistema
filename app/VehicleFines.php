<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleFines extends Model
{
	protected $fillable =
	[
		'real_employee_id',
		'status',
		'date',
		'payment_date',
		'payment_limit_date',
		'total',
		'vehicles_id',
		'users_id',
	];

	public function driverData()
	{
		return $this->hasOne(RealEmployee::class,'id','real_employee_id');
	}

	public function documents()
	{
		return $this->hasMany(VehicleDocument::class,'vehicles_fines_id','id');
	}
}
