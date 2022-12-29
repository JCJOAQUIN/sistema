<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MassiveEmployee extends Model
{
	protected $fillable = 
	[
		'idEmployee',
		'idCreator',
		'csv',
	];
}
