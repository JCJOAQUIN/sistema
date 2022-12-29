<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idstate';
	protected $fillable   = 
	[
		'description',
		'c_state',
		'status',
	];

	public function state()
	{
		return $this->hasOne(Enterprise::class,'state_idstate','idstate');
	}

	public function scopeOrderName($query)
	{
		return $query->orderBy('description','asc');
	}
}
