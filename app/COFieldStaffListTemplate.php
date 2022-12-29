<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COFieldStaffListTemplate extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'group',
		'groupId',
		'category',
	];
}
