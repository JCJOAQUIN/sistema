<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ControlRequisition extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'data_remittances',
		'cost_center',
		'WBS',
		'frentes',
		'EDT',
		'cost_type',
		'cost_description',
		'work_area',
		'data_requisition',
		'requisition',
		'applicant',
	];

	public function controlInternal()
	{
		return $this->hasOne(ControlInternal::class, 'control_requisitions_id');
	}
}
