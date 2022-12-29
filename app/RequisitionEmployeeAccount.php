<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequisitionEmployeeAccount extends Model
{
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
}
