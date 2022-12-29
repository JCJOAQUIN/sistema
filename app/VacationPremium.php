<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VacationPremium extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idvacationPremium';
	protected $fillable   = 
	[
		'idvacationPremium',
		'idnominaEmployee',
		'dateOfAdmission',
		'sd',
		'sdi',
		'workedDays',
		'holidaysDays',
		'holidays',
		'exemptHolidayPremium',
		'holidayPremiumTaxed',
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
		return $this->hasMany(NominaEmployeeAccounts::class,'idvacationPremium','idvacationPremium');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}
}
