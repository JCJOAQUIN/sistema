<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CostOverrunsNCGCompetition extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'fechadeconcurso',
		'numerodeconcurso',
		'direcciondeconcurso',
	];
}
