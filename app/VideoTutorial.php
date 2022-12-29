<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoTutorial extends Model
{
	protected $fillable = 
	[
		'name',
		'url',
		'module_id',
	];
}
