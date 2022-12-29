<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idSettlement';
	protected $fillable   = 
	[
		'idnominaEmployee',
		'sd',
		'sdi',
		'fullYears',
		'holidaysDays',
		'holidayDaysPerYearOfService',
		'daysOfBonus',
		'seniorityPremium',
		'holidays',
		'exemptBonus',
		'taxableBonus',
		'exemptHolidayPremium',
		'holidayPremiumTaxed',
		'profitSharing',
		'subsidy',
		'totalPerceptions',
		'isr',
		'totalTaxes',
		'netIncome',
		'idpaymentMethod',
		'idemployeeAccounts',
	];

	public function employeeAccounts()
	{
		return $this->hasMany(EmployeeAccount::class,'id','idemployeeAccounts');
	}

	public function nominaEmployeeAccounts()
	{
		return $this->hasMany(NominaEmployeeAccounts::class,'idSettlement','idSettlement');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}
}
