<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillNomina extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'type',
		'paymentDate',
		'paymentStartDate',
		'paymentEndDate',
		'paymentDays',
		'perceptions',
		'deductions',
		'other_payments',
		'employer_register',
		'bill_id',
	];

	public function nominaPerception()
	{
		return $this->hasMany(BillNominaPerception::class,'bill_nomina_id');
	}

	public function nominaDeduction()
	{
		return $this->hasMany(BillNominaDeduction::class,'bill_nomina_id');
	}

	public function nominaOtherPayment()
	{
		return $this->hasMany(BillNominaOtherPayment::class,'bill_nomina_id');
	}

	public function nominaIndemnification()
	{
		return $this->hasOne(BillNominaIndemnification::class,'bill_nomina_id');
	}

	public function nominaExtraHours()
	{
		return $this->hasMany(BillNominaExtraHours::class,'bill_nomina_id');
	}
}