<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
	protected $primaryKey = 'idProvider';
	protected $fillable   = 
	[
		'idProvider',
		'businessName',
		'beneficiary',
		'phone',
		'rfc',
		'contact',
		'commentaries',
		'status',
		'users_id',
		'address',
		'number',
		'colony',
		'postalCode',
		'city',
		'state_idstate',
		'provider_data_id',
	];
	const CREATED_AT      = 'created';

	public function requests()
	{
		return $this->hasMany(Purchase::class,'idProvider','idProvider');
	}

	public function providerBank()
	{
		return $this->hasMany(ProviderBanks::class,'provider_idProvider','idProvider');
	}

	public function providerClassification()
	{
		return $this->hasMany(ProviderClassification::class,'provider_id','idProvider');
	}

	public function providerData()
	{
		return $this->hasOne(ProviderData::class,'id','provider_data_id');
	}
}
