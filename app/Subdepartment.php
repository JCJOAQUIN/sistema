<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subdepartment extends Model
{
	protected $fillable = 
	[
		'name',
		'status'
	];

	public function scopeOrderName($query)
	{
		return $query->orderBy('name','asc');
	}
}
