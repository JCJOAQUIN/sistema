<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
	protected $fillable = 
	[
		'place',
		'status',
	];

	public function scopeOrderName($query)
	{
		return $query->orderBy('place','asc');
	}
}
