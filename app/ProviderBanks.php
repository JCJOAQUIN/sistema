<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderBanks extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'provider_idProvider',
		'banks_idBanks',
		'account',
		'branch',
		'reference',
		'clabe',
		'currency',
		'agreement',
		'visible',
		'iban',
		'bic_swift',
	];

	public function bank()
	{
		return $this->belongsTo(Banks::class,'banks_idBanks','idBanks');
	}

	public function provider()
	{
		return $this->belongsTo(Provider::class,'provider_idProvider','idProvider');
	}
}
