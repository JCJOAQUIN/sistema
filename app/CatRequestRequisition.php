<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatRequestRequisition extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
	];
}
