<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkOrderDetail extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idWorkOrder',
		'part',
		'quantity',
		'unit',
		'description',
	];
}
