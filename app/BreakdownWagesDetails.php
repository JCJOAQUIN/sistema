<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BreakdownWagesDetails extends Model
{
	protected $fillable = 
	[
		'idUpload',
		'groupName',
		'code',
		'concept',
		'measurement',
		'baseSalaryPerDay',
		'realSalaryFactor',
		'realSalary',
		'viatics',
		'feeding',
		'totalSalary',
	];
}
