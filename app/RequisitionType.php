<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequisitionType extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
		'status',
	];

	public function warehouse()
	{
		return $this->hasMany(CatWarehouseType::class,'requisition_types_id','id');
	}
}
