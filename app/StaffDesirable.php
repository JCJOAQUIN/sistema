<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StaffDesirable extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idStaff',
		'desirable',
		'description',
	];

	public function staff()
	{
		return $this->hasMany(Staff::class,'idStaff','idStaff');
	}
}