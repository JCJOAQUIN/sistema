<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnitPricesDetails extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'code',
		'concept',
		'measurement',
		'amount',
		'price',
		'import',
		'incidence',
		'father',
		'type',
		'op',
	];

	public function childrens()
	{
		return $this->hasMany(UnitPricesDetails::class,'father','id');
	}

	public function parent()
	{
		return $this->belongsTo(UnitPricesDetails::class,'father','id');
	}
}
