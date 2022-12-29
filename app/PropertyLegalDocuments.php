<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PropertyLegalDocuments extends Model
{
	protected $fillable = 
	[
		'description',
		'path',
		'property_id',
		'user_id',
	];
}
