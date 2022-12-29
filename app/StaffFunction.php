<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StaffFunction extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idStaff',
		'function',
		'description',
	];

	public function staff()
	{
		return $this->hasMany(Staff::class,'idStaff','idStaff');
	}
}