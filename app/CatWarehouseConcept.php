<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatWarehouseConcept extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'description',
		'warehouseType',
	];
}
