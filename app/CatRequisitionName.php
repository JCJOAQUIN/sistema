<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatRequisitionName extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
	];
}
