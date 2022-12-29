<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryRqUnit extends Model
{
	public $timestamps    = false;
	public $incrementing  = false;
	protected $primaryKey = null;
	protected $fillable   = 
	[
		'unit_id',
		'rq_id',
		'category_id',
	];

	public function rq_type()
	{
		return $this->hasOne(RequisitionType::class,'id','rq_id');
	}

	public function category()
	{
		return $this->hasOne(CatWarehouseType::class,'id','category_id');
	}
}
