<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
	protected $primaryKey = 'idClient';
	protected $fillable   = 
	[
		'idClient',
		'businessName',
		'email',
		'phone',
		'rfc',
		'contact',
		'commentaries',
		'status',
		'users_id',
		'address',
		'number',
		'colony',
		'postalCode',
		'city',
		'state_idstate',
	];
	const CREATED_AT      = 'created';

	public function requests()
	{
		return $this->hasMany(Income::class,'idClient','idClient');
	}

	public function state()
	{
		return $this->belongsTo(State::class,'state_idstate','idstate');
	}
}
