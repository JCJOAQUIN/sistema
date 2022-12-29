<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NominaEmployeeNF extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idnominaemployeenf';
	protected $fillable   = 
	[
		'idnominaemployeenf',
		'idnominaEmployee',
		'idpaymentMethod',
		'idemployeeAccounts',
		'reference',
		'discount',
		'reasonDiscount',
		'amount',
		'reasonAmount',
		'netIncome',
	];

	public function employeeAccounts()
	{
		return $this->hasMany(EmployeeAccount::class,'id','idemployeeAccounts');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}

	public function discounts()
	{
		return $this->hasMany(DiscountsNomina::class,'idnominaemployeenf','idnominaemployeenf')->where('reason','NOT LIKE','%'.'infonavit'.'%');
	}

	public function discountInfonavit()
	{
		return $this->hasMany(DiscountsNomina::class,'idnominaemployeenf','idnominaemployeenf')->where('reason','LIKE','%'.'infonavit'.'%');
	}

	public function extras()
	{
		return $this->hasMany(ExtrasNomina::class,'idnominaemployeenf','idnominaemployeenf');
	}

	public function payroll_receipt()
	{
		return $this->hasOne(PayrollReceipt::class,'idnominaemployeenf');
	}
}
