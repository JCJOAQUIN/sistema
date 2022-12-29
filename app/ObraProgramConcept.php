<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ObraProgramConcept extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'father',
		'code',
		'concept',
		'measurement',
	];

	public function childrens()
	{
		return $this->hasMany(ObraProgramConcept::class,'father','id');
	}

	public function parent()
	{
		return $this->belongsTo(ObraProgramConcept::class,'father','id');
	}

	public function details()
	{
		return $this->hasMany(ObraProgramDetails::class,'idObraProgramConcept','id');
	}
}
