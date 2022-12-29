<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
	protected $primaryKey = 'idExpenses';
	public $timestamps    = false;
	protected $fillable   = 
	[
		'idExpenses',
		'idFolio',
		'idKind',
		'resourceId',
		'total',
		'idEmployee',
		'idUsers',
		'reembolso',
		'reintegro',
		'reference',
		'idpaymentMethod',
	];

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function expensesDetail()
	{
		return $this->hasMany(ExpensesDetail::class,'idExpenses','idExpenses');
	}

	public function bankData()
	{
		return $this->belongsTo(Employee::class,'idEmployee','idEmployee');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}

	public function resourceData()
	{
		return $this->hasOne(Resource::class,'idFolio','resourceId');
	}

}
