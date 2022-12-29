<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProfitSharing extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idprofitSharing';
	protected $fillable   = 
	[
		'idprofitSharing',
		'idnominaEmployee',
		'sd',
		'sdi',
		'workedDays',
		'totalSalary',
		'ptuForDays',
		'ptuForSalary',
		'totalPtu',
		'exemptPtu',
		'taxedPtu',
		'subsidy',
		'totalPerceptions',
		'isrRetentions',
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
		return $this->hasMany(NominaEmployeeAccounts::class,'idprofitSharing','idprofitSharing');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}
}
