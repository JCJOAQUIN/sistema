<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BalanceSheetYears extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idBalanceSheet',
		'year',
	];
}
