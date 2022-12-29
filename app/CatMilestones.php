<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatMilestones extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
	];
}
