<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AutomaticRequests extends Model
{
	protected $fillable = 
	[
		'kind',
		'taxPayment',
		'idAccAcc',
		'idEnterprise',
		'idArea',
		'idDepartment',
		'idProject',
		'idRequest',
		'idElaborate',
		'created_at',
		'periodicity',
		'day_monthlyOn',
		'day_twiceMonthly_one',
		'day_twiceMonthly_one',
	];

	public function dataKind()
	{
		return $this->hasOne(RequestKind::class,'idrequestkind','kind');
	}

	public function purchase()
	{
		return $this->hasOne(PurchaseTemp::class,'idAutomaticRequests','id');
	}

	public function resource()
	{
		return $this->hasOne(ResourceTemp::class,'idAutomaticRequests','id');
	}

	public function requestUser()
	{
		return $this->hasOne(User::class,'id','idRequest');
	}

	public function enterprise()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterprise');
	}
}
