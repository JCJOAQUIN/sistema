<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idresource';
	protected $fillable   = 
	[
		'total',
		'reference',
		'idEmployee',
		'idFolio',
		'idKind',
		'idUsers',
		'idpaymentMethod',
	];

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}

	public function resourceDetail()
	{
		return $this->hasMany(ResourceDetail::class,'idresource','idresource');
	}

	public function expensesRequest()
	{
		return $this->hasMany(Expenses::class,'resourceId','idFolio');
	}

	public function bankData()
	{
		return $this->belongsTo(Employee::class,'idEmployee','idEmployee');
	}

	public function documents()
	{
		return $this->hasMany(ResourceDocument::class,'resource_id','idresource');
	}
}
