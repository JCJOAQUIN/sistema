<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParameterVacation extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'text',
		'fromYear',
		'toYear',
		'days',
	];
}
