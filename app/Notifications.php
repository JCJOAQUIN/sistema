<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'title',
		'body',
		'end',
		'route',
		'user_id',
	];
}
