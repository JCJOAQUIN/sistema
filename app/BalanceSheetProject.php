<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BalanceSheetProject extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idBalanceSheet',
		'idProject'
	];
}
