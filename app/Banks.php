<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Banks extends Model
{
	protected $primaryKey = 'idBanks';
	protected $keyType    = 'string';
	public $incrementing  = false;
	public $timestamps    = false;

	protected $fillable = 
	[
		'idBanks',
		'description',
	];

	public function employees()
	{
		return $this->hasMany(Employee::class,'idBanks','idBanks');
	}

	public function scopeOrderName($query)
	{
		return $query->orderBy('description','asc');
	}
}