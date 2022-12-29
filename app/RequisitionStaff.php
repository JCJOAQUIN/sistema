<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequisitionStaff extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'boss_id',
		'staff_reason',
		'staff_position',
		'staff_periodicity',
		'staff_schedule_start',
		'staff_schedule_end',
		'staff_min_salary',
		'staff_max_salary',
		'staff_s_description',
		'staff_habilities',
		'staff_experience',
		'requisition_id',
	];

	public function boss()
	{
		return $this->hasOne(User::class,'id','boss_id');
	}
}
