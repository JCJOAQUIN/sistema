<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderData extends Model
{
	protected $fillable = 
	[
		'id',
		'users_id',
	];
	const CREATED_AT      = 'created';
	public function providerBank()
	{
		return $this->hasMany(ProviderBanks::class,'provider_data_id','id')->where('visible',1);
	}
	public function providerBankToShow($id)
	{
		return $this->hasMany(ProviderBanks::class,'provider_data_id','id')->where('id',$id);
	}
	public function providerClassification()
	{
		return $this->hasOne(ProviderClassification::class,'provider_data_id','id');
	}
}
