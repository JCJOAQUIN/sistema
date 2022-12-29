<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COFieldStaffGeneralTemplate extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'montototal',
		'porcentaje',
	];
}
