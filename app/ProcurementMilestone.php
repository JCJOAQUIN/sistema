<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcurementMilestone extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'seq_num',
		'milestone',
		'schedule',
		'status',
		'complete_status',
		'idprocurementPurchase',
		'users_id',
		'created_at',
	];
	protected $casts = 
	[
		'schedule' => 'datetime:Y-m-d',
		'complete_status' => 'datetime:Y-m-d',
	];
}
