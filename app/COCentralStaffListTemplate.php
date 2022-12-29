<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COCentralStaffListTemplate extends Model
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
