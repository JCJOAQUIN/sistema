<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillDetail extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idBillDetail';
	protected $fillable   = 
	[
		'keyProdServ',
		'keyUnit',
		'quantity',
		'description',
		'value',
		'amount',
		'discount',
		'idBill',
	];

	public function taxes()
	{
		return $this->hasMany(BillTaxes::class,'idBillDetail','idBillDetail');
	}

	public function taxesRet()
	{
		return $this->hasMany(BillTaxes::class,'idBillDetail','idBillDetail')->where('type','Retención');
	}

	public function taxesTras()
	{
		return $this->hasMany(BillTaxes::class,'idBillDetail','idBillDetail')->where('type','Traslado');
	}

	public function taxesTrasIva()
	{
		return $this->hasMany(BillTaxes::class,'idBillDetail','idBillDetail')->where('type','Traslado')->where('tax','002');
	}

	public function cfdi_product()
	{
		return $this->hasOne(CatProdServ::class,'keyProdServ','keyProdServ');
	}

	public function cfdi_unity()
	{
		return $this->hasOne(CatUnity::class,'keyUnit','keyUnit');
	}
}
