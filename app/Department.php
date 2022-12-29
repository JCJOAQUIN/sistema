<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
	protected $fillable = 
	[
		'name',
		'details',
		'status',
	];

	public function user()
	{
		return $this->hasOne(User::class,'departament_id','id');
	}

	public function inCharge()
	{
		return $this->belongsToMany(User::class,'user_has_department');
	}

	public function scopeOrderName($query)
	{
		return $query->orderBy('name','asc');
	}
}
