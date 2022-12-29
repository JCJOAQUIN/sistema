<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleOwner extends Model
{
	protected $fillable =
	[
		'name',
		'last_name',
		'scnd_last_name',
		'curp',
		'rfc',
		'imss',
		'email',
		'street',
		'number',
		'colony',
		'cp',
		'city',
		'state_id',
		'users_id',
	];

	public function scopeOrderName($query)
	{
		return $query->orderBy('name','asc')->orderBy('last_name','asc')->orderBy('scnd_last_name','asc');
	}

	public function fullName()
	{
		return $this->name.' '.$this->last_name.' '.$this->scnd_last_name;
	}
}
