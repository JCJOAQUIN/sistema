<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParameterSubsidy extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'inferior',
		'superior',
		'subsidy',
		'lapse',
	];
}
