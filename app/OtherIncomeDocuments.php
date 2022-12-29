<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OtherIncomeDocuments extends Model
{
	protected $fillable = 
	[
		'idOtherIncome',
		'path',
		'name',
	];
}
