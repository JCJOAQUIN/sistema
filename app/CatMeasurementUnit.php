<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatMeasurementUnit extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'description',
	];
}
