<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
	protected $fillable = 
	[
		'brand',
		'sub_brand',
		'model',
		'serial_number',
		'plates',
		'kilometer',
		'vehicle_status',
		'fuel_type',
		'tag',
		'date_verification',
		'company',
		'expiration_date',
		'enterprise_id',
		'real_employee_id',
		'vehicles_owners_id',
		'users_id',
	];

	public function dataKilometers()
	{
		return $this->hasMany(Kilometers::class,'vehicles_id','id');
	}
	public function dataOwnerMoral()
	{
		return $this->hasOne(Enterprise::class,'id','enterprise_id');
	}

	public function dataOwnerPhysical()
	{
		return $this->hasOne(RealEmployee::class,'id','real_employee_id');
	}

	public function dataOwnerExternal()
	{
		return $this->hasOne(VehicleOwner::class,'id','vehicles_owners_id');
	}

	public function taxes()
	{
		return $this->hasMany(VehicleTaxes::class,'vehicles_id','id');
	}

	public function fines()
	{
		return $this->hasMany(VehicleFines::class,'vehicles_id','id');
	}

	public function fuel()
	{
		return $this->hasMany(VehicleFuel::class,'vehicles_id','id');
	}

	public function mechanicalServices()
	{
		return $this->hasMany(VehicleMechanicalService::class,'vehicles_id','id');
	}

	public function insurances()
	{
		return $this->hasMany(VehicleInsurance::class,'vehicles_id','id');
	}

	public function documentsTechnical()
	{
		return $this->hasMany(VehicleDocument::class,'vehicles_id','id')->where('cat_type_document_id',1);
	}

	public function documentsOwner()
	{
		return $this->hasMany(VehicleDocument::class,'vehicles_id','id')->where('cat_type_document_id',2);
	}
}
