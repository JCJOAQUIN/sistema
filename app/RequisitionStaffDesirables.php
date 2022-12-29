<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequisitionStaffDesirables extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'desirable',
		'description',
		'requisition_id',
	];
}
