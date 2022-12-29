<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatWarehouseType extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'description',
		'status',
		'requisition_types_id'
	];
}
