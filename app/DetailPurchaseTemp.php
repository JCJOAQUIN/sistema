<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailPurchaseTemp extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idPurchaseTemp',
		'quantity',
		'description',
		'unitPrice',
		'tax',
		'amount',
		'typeTax',
		'subtotal',
		'statusWarehouse',
		'commentaries',
	];

	public function taxes()
	{
		return $this->hasMany(TaxesPurchaseTemp::class,'idDetailPurchaseTemp','id');
	}

	public function retentions()
	{
		return $this->hasMany(RetentionPurchaseTemp::class,'idDetailPurchaseTemp','id');
	}
}
