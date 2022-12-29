<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RealEmployeeDocument extends Model
{
	protected $fillable = 
	[
		'name',
		'path',
		'real_employee_id',
	];
}
