<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NominaEmployeeAccounts extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idEmployeeAccounts',
		'idnominaemployeenf',
		'idSalary',
		'idBonus',
		'idLiquidation',
		'idSettlement',
		'idvacationPremium',
		'idprofitSharing',
	];

	public function employeeAccounts()
	{
		return $this->hasMany(EmployeeAccount::class,'id','idEmployeeAccounts');
	}
}
