<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupingHasAccount extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idGroupingAccount',
		'idEnterprise',
		'idAccAcc',
	];
}
