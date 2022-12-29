<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderSecondaryPrice extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idProviderSecondary',
		'user_id',
		'idRequisitionDetail',
		'unitPrice',
		'subtotal',
		'typeTax',
		'iva',
		'total',
	];

	public function taxesData()
	{
		return $this->hasMany(ProviderSecondaryPriceTaxes::class,'providerSecondaryPrice_id','id')->where('type',1);
	}

	public function retentionsData()
	{
		return $this->hasMany(ProviderSecondaryPriceTaxes::class,'providerSecondaryPrice_id','id')->where('type',2);
	}
}
