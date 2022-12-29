<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupsTaxes extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idgroupsTaxes';
	protected $fillable   = 
	[
		'name',
		'amount',
		'idgroupsDetail',
	];
}
