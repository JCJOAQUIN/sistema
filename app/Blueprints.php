<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Blueprints extends Model
{
    public $table = 'blueprints';
	protected $fillable = 
	[
		'name',
		'wbs_id',
		'project_id',
	];
}