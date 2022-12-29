<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BalanceSheet extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'status',
		'type',
		'users_id',
		'date',
		'file',
	];

	public function dataEnterprise()
	{
		return $this->hasOne(BalanceSheetEnterprise::class,'idBalanceSheet','id');
	}

	public function dataProject()
	{
		return $this->hasMany(BalanceSheetProject::class,'idBalanceSheet','id')->select('idProject');
	}

	public function dataMonths()
	{
		return $this->hasMany(BalanceSheetMonths::class,'idBalanceSheet','id');
	}

	public function dataYears()
	{
		return $this->hasOne(BalanceSheetYears::class,'idBalanceSheet','id');
	}
}
