<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcurementPurchaseRemarks extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'remark',
		'date',
		'created_at',
		'users_id',
		'idprocurementPurchase',
	];
	protected $casts = 
	[
		'date' => 'datetime:Y-m-d',
	];
}
