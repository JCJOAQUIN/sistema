<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idSalary';
	protected $fillable   = 
	[
		'idnominaEmployee',
		'sd',
		'sdi',
		'workedDays',
		'daysForImss',
		'salary',
		'loan_perception',
		'puntuality',
		'assistance',
		'subsidy',
		'totalPerceptions',
		'imss',
		'infonavit',
		'fonacot',
		'loan_retention',
		'isrRetentions',
		'totalRetentions',
		'netIncome',
		'subsidyCaused',
		'infonavitComplement',
		'idpaymentMethod',
		'idemployeeAccounts',
	];

	public function employeeAccounts()
	{
		return $this->hasMany(EmployeeAccount::class,'id','idemployeeAccounts');
	}

	public function nominaEmployeeAccounts()
	{
		return $this->hasMany(NominaEmployeeAccounts::class,'idSalary','idSalary');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}
}
