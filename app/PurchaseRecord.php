<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseRecord extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idFolio',
		'idKind',
		'title',
		'datetitle',
		'reference',
		'notes',
		'paymentMethod',
		'typeCurrency',
		'billStatus',
		'subtotal',
		'tax',
		'amount_taxes',
		'amount_retention',
		'total',
		'numberOrder',
		'provider',
	];

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function detailPurchase()
	{
		return $this->hasMany(PurchaseRecordDetail::class,'idPurchaseRecord','id');
	}

	public function documents()
	{
		return $this->hasMany(PurchaseRecordDocuments::class,'idPurchaseRecord','id');
	}

	public function enterprisePayment()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterprisePayment');
	}

	public function accountPayment()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccAccPayment');
	}
}
