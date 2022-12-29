<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseTemp extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idProvider',
		'idKind',
		'title',
		'datetitle',
		'reference',
		'notes',
		'discount',
		'paymentMode',
		'typeCurrency',
		'billStatus','path',
		'subtotal',
		'tax',
		'amount',
		'provider_has_banks_id',
		'numberOrder',
		'idAutomaticRequests',
	];

	public function detailPurchase()
	{
		return $this->hasMany(DetailPurchaseTemp::class,'idPurchaseTemp','id');
	}

	public function provider()
	{
		return $this->hasOne(Provider::class,'idProvider','idProvider');
	}

	public function bankData()
	{
		return $this->belongsTo(ProviderBanks::class,'provider_has_banks_id','id');
	}
}
