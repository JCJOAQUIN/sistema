<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idEmployee';
	protected $fillable   = 
	[
		'idEmployee',
		'alias',
		'clabe',
		'account',
		'cardNumber',
		'idBanks',
		'idKindOfBank',
		'idUsers',
		'visible',
	];

	public function bank()
	{
		return $this->belongsTo(Banks::class,'idBanks','idBanks');
	}

	public function user()
	{
		return $this->belongsTo(User::class,'idUsers','id');
	}
}
