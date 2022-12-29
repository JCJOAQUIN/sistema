<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatBuyer extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
	];
}
