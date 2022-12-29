<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatTypePayroll extends Model
{
	public $timestamps   = false;
	public $incrementing = false;
	protected $keyType   = 'string';
	protected $fillable  = 
	[
		'description',
	];

	public function scopeOrderName($query)
	{
		return $query->orderBy('description','asc');
	}
	
}
