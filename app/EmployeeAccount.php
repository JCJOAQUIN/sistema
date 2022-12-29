<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeAccount extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idEmployee',
		'alias',
		'clabe',
		'account',
		'cardNumber',
		'branch',
		'idCatBank',
		'recorder',
		'visible',
	];

	public function bank()
	{
		return $this->belongsTo(CatBank::class,'idCatBank','c_bank');
	}

	public function employee()
	{
		return $this->hasOne(RealEmployee::class,'id','idEmployee');
	}
}
