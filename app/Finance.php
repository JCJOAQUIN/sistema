<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Finance extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idFolio',
		'idKind',
		'title',
		'datetitle',
		'kind',
		'paymentMethod',
		'bank',
		'account',
		'card',
		'currency',
		'subtotal',
		'tax',
		'taxType',
		'amount',
		'note',
		'week',
	];

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function banks()
	{
		return $this->hasOne(Banks::class,'idBanks','bank');
	}

	public function bankAccount()
	{
		return $this->hasOne(BankAccount::class,'id','account');
	}

	public function creditCard()
	{
		return $this->hasOne(CreditCards::class,'idcreditCard','card');
	}
}
