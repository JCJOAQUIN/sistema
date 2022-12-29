<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatCurrency extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'currency',
		'description',
	];
}
