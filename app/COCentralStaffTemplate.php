<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COCentralStaffTemplate extends Model
{
    public $timestamps = false;
	protected $fillable   = 
	[
		'idUpload',
		'group',
		'groupId',
		'category',
		'amount',
		'salary',
		'import',
		'factor1',
		'factor2',
	];
}
