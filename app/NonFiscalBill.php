<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NonFiscalBill extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idBill';
	protected $fillable   = 
	[
		'rfc',
		'businessName',
		'taxRegime',
		'clientRfc',
		'clientBusinessName',
		'expeditionDate',
		'conditions',
		'status',
		'subtotal',
		'discount',
		'total',
		'paymentMethod',
		'paymentWay',
		'currency',
	];

	public function billDetail()
	{
		return $this->hasMany(NonFiscalBillDetail::class,'idBill','idBill');
	}

	public function cfdiPaymentWay()
	{
		return $this->hasOne(CatPaymentWay::class,'paymentWay','paymentWay');
	}

	public function cfdiPaymentMethod()
	{
		return $this->hasOne(CatPaymentMethod::class,'paymentMethod','paymentMethod');
	}

	public function requestHasBill()
	{
		return $this->hasOne(RequestModel::class,'folio','folio');
	}
}
