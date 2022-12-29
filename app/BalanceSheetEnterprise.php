<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BalanceSheetEnterprise extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idBalanceSheet',
		'idEnterprise',
	];

	public function enterprise()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterprise');
	}
}
