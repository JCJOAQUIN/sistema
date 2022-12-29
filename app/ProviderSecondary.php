<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderSecondary extends Model
{
	const CREATED_AT    = 'created';
	protected $fillable = 
	[
		'businessName',
		'rfc',
		'phone',
		'contact',
		'beneficiary',
		'commentaries',
		'address',
		'number',
		'colony',
		'postalCode',
		'city',
		'state_idstate',
		'status',
		'users_id',
	];
	
	public function accounts()
	{
		return $this->hasMany(ProviderSecondaryAccounts::class,'idProviderSecondary','id');
	}
}
