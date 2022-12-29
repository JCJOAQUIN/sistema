<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatElements extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
	];
}
