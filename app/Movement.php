<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
	const CREATED_AT      = 'date_creator';
	protected $primaryKey = 'idmovement';
	protected $fillable   = 
	[
		'movementDate',
		'amount',
		'description',
		'commentaries',
		'statusConciliation',
		'idEnterprise',
		'idAccount',
		'idpayment',
		'conciliationDate',
		'movementType',
		'creator',
	];

	public function enterprise()
	{
		return $this->belongsTo(Enterprise::class,'idEnterprise','id');
	}

	public function bill()
	{
		return $this->hasOne(Bill::class,'idBill','idBill');
	}

	public function accounts()
	{
		return $this->belongsTo(Account::class,'idAccount','idAccAcc');
	}

	public function payments()
	{
		return $this->hasMany(Payment::class,'idpayment','idpayment');
	}

	public function payment()
	{
		return $this->hasOne(Payment::class,'idpayment','idpayment');   
	}
}
