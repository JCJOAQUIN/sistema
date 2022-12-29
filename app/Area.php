<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
	protected $fillable =
	[
		'id',
		'name',
		'details',
		'responsable',
		'status',
	];

	public function user()
	{
		return $this->hasOne(User::class,'area_id','id');
	}
	
	public function scopeOrderName($query)
	{
		return $query->orderBy('name','asc');
	}

}
