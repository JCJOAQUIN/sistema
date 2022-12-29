<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplieDetails extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'groupName',
		'code',
		'concept',
		'measurement',
		'date',
		'amount',
		'price',
		'import',
		'incidence',
	];
}
