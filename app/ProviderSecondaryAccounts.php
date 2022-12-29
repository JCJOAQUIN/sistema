<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderSecondaryAccounts extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idProviderSecondary', 
		'idBanks', 
		'alias', 
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
		return $this->belongsTo(Banks::class,'idBanks','idBanks');
	}
}
