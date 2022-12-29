<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatMeasurementTypes extends Model
{
	public $timestamps = false;
	protected $fillable = 
	[
		'description',
		'type',
		'equivalence',
		'father',
		'child_order',
	];

	public function childrens()
	{
	 return $this->hasMany(CatMeasurementTypes::class,'father');
	}

	public function parent()
	{
		return $this->belongsTo(CatMeasurementTypes::class,'father','id');
	}
}
