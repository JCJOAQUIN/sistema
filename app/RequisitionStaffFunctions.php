<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequisitionStaffFunctions extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'function',
		'description',
		'requisition_id',
	];
}
