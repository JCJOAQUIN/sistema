<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NominaEmployeeF extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idnominaemployeef';
	protected $fillable   = 
	[
		'idnominaemployeef',
		'idnominaEmployee',
		'idpaymentMethod',
		'idEmployeeAccounts',
		'reference',
		'discount',
		'reasonDiscount',
		'amount',
		'reasonAmount',
	];

	public function employeeAccounts()
	{
		return $this->hasMany(EmployeeAccount::class,'id','idEmployeeAccounts');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}
}
