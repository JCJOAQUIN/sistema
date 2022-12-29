<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeSubdepartment extends Model
{
	protected $fillable = 
	[
		'working_data_id',
		'subdepartment_id'
	];

	public function dataSubdepartment()
	{
		return $this->hasOne(Subdepartment::class,'id','subdepartment_id');
	}
}
