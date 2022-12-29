<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupsRetention extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idgroupsRetention';
	protected $fillable   = 
	[
		'name',
		'amount',
		'idgroupsDetail',
	];
}
