<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
	protected $primaryKey = 'idBonus';
	public $timestamps    = false;
	protected $fillable   = 
	[
		'idBonus',
		'idnominaEmployee',
		'sd',
		'sdi',
		'dateOfAdmission',
		'daysForBonuses',
		'proportionalPartForChristmasBonus',
		'exemptBonus',
		'taxableBonus',
		'totalPerceptions',
		'isr',
		'totalTaxes',
		'netIncome',
		'idpaymentMethod',
		'idemployeeAccounts',
		'totalIncomeBonus'
	];

	public function employeeAccounts()
	{
		return $this->hasMany(EmployeeAccount::class,'id','idemployeeAccounts');
	}

	public function nominaEmployeeAccounts()
	{
		return $this->hasMany(NominaEmployeeAccounts::class,'idBonus','idBonus');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}
}
