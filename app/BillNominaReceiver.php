<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillNominaReceiver extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'curp',
		'contractType_id',
		'regime_id',
		'employee_id',
		'periodicity',
		'c_state',
		'nss',
		'laboralDateStart',
		'antiquity',
		'job_risk',
		'sdi',
		'bill_id',
	];

	public function nominaContract()
	{
		return $this->hasOne(CatContractType::class,'id','contractType_id');
	}

	public function nominaRegime()
	{
		return $this->hasOne(CatRegimeType::class,'id','regime_id');
	}

	public function nominaPeriodicity()
	{
		return $this->hasOne(CatPeriodicity::class,'c_periodicity','periodicity');
	}

	public function nominaPositionRisk()
	{
		return $this->hasOne(CatPositionRisk::class,'id','job_risk');
	}

	public function state()
	{
		return $this->hasOne(State::class,'c_state','c_state');
	}
}