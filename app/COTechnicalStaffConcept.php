<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COTechnicalStaffConcept extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'parent',
		'category',
		'measurement',
		'total',
	];

	public function childrens()
	{
		return $this->hasMany(COTechnicalStaffConcept::class,'parent');
	}

	public function father()
	{
		return $this->belongsTo(COTechnicalStaffConcept::class,'parent','id');
	}
}
