<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillPayment extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idBill',
		'paymentDate',
		'paymentWay',
		'amount',
	];

	public function bill()
	{
		return $this->belongsTo(Bill::class,'idBill','idBill');
	}

	public function complementPaymentWay()
	{
		return $this->hasOne(CatPaymentWay::class,'paymentWay','paymentWay')
			->withoutGlobalScopes();
	}

	public function complementCurrency()
	{
		return $this->hasOne(CatCurrency::class,'currency','currency');
	}
}
