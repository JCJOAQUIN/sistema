<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MobileSession extends Model
{
	protected $fillable   = 
	[
		'user_id',
		'user_kind',
		'token'
	];
}
