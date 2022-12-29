<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BoardroomElements extends Model
{
	protected $fillable = 
	[
		'quantity',
		'element_id',
		'boardroom_id',
		'description',
	];

	public function boardroom()
	{
		return $this->belongsTo(Boardroom::class,'id','boardroom_id');
	}

	public function elemet_description()
	{
		return $this->hasOne(CatElements::class,'id','element_id');
	}

	public function getElementAttribute()
	{
		return $this->elemet_description->name;
	}
}
