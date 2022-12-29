<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
	public $timestamps    = false;
	public $incrementing  = false;
	protected $primaryKey = 'parameter_name';
	protected $keyType    = 'string';
	protected $fillable   = 
	[
		'parameter_name',
		'description',
		'category',
		'parameter_value',
		'prefix',
		'suffix',
		'validation',
	];
}
