<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatExpeditor extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
	];
}
