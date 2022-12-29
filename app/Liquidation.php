<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Liquidation extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idLiquidation';
	protected $fillable   = 
	[
		'idLiquidation',
		'idnominaEmployee',
		'sd',
		'sdi',
		'admissionDate',
		'downDate',
		'fullYears',
		'workedDays',
		'holidayDays',
		'bonusDays',
		'liquidationSalary',
		'twentyDaysPerYearOfServices',
		'seniorityPremium',
		'exemptCompensation',
		'taxedCompensation',
		'holidays',
		'exemptBonus',
		'taxableBonus',
		'holidayPremiumExempt',
		'holidayPremiumTaxed',
		'otherPerception',
		'totalPerceptions',
		'isr',
		'totalRetentions',
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
		return $this->hasMany(NominaEmployeeAccounts::class,'idLiquidation','idLiquidation');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}
}
