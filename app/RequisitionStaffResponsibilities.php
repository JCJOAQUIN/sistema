<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequisitionStaffResponsibilities extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'staff_responsibilities',
		'requisition_id',
	];

	public function dataResponsibilities()
	{
		return $this->hasOne(Responsibility::class,'id','staff_responsibilities');
	}
}
