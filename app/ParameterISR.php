<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParameterISR extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'inferior',
		'superior',
		'quota',
		'excess',
		'lapse',
	];
}
