<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idpayment';
	protected $fillable   = 
	[
		'idpayment',
		'tax',
		'retention',
		'amount',
		'paymentDate',
		'elaborateDate',
		'idFolio',
		'idKind',
		'idRequest',
		'idEnterprise',
		'account',
		'path',
		'commentaries',
		'statusConciliation',
		'idnominaEmployee',
		'exchange_rate',
		'exchange_rate_description',
		'partial_id',
	];

	public function partialPayment()
	{
		return $this->belongsTo(PartialPayment::class,'partial_id','id');
	}

	public function request()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function accounts()
	{
		return $this->belongsTo(Account::class,'account','idAccAcc');
	}

	public function accountData()
	{
		return $this->hasOne(Account::class,'idAccAcc','account');
	}

	public function enterprise()
	{
		return $this->belongsTo(Enterprise::class,'idEnterprise','id');
	}

	public function documentsPayments()
	{
		return $this->hasMany(DocumentsPayments::class,'idpayment','idpayment');
	}

	public function nominaEmployee()
	{
		return $this->belongsTo(NominaEmployee::class,'idnominaEmployee','idnominaEmployee');
	}

	public function movement()
	{
		return $this->hasOne(Movement::class,'idmovement','idmovement');
	}

	public function partialPayments()
	{
		return $this->hasMany(PartialPayment::class,'payment_id','idpayment');
	}
}
