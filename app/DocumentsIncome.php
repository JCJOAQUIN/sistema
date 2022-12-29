<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentsIncome extends Model
{
	protected $fillable = 
	[
		'idIncome',
		'path',
		'name',
	];
}
