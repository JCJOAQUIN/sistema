<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KindOfBanks extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idKindBank';
	protected $fillable   = 
	[
		'idKindBank',
		'description',
	];

	public function scopeOrderName($query)
	{
		return $query->orderBy('description','asc');
	}
}
