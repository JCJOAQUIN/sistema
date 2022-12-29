<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NominaDocuments extends Model
{
	protected $fillable = 
	[
		'idnominaEmployee',
		'name',
		'path',
	];
}
