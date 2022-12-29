<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Responsibility extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'responsibility',
		'description',
	];

	public function staff()
	{
		return $this->belongsToMany(Staff::class,'staff_responsibilities','idResponsibility','idStaff');
	}

	public function scopeOrderName($query)
	{
		return $query->orderBy('description','asc');
	}

}
