<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COEnterpriseDocument extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'name',
	];
}