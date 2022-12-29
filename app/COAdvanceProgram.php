<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COAdvanceProgram extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'idProgramado',
		'parcial',
		'acumulado',
	];
}
