<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BalanceSheetMonths extends Model
{
	public $timestamps  = false;
	protected $fillable =
	[
		'idBalanceSheet',
		'month',
	];
}
