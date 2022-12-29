<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeWBS extends Model
{
	protected $table = 'employee_w_b_s';
	protected $fillable = 
	[
		'working_data_id',
		'cat_code_w_bs_id'
	];
}
