<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COTechnicalStaffSalaryConcept extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'type',
		'parent',
		'category',
		'measurement',
		'amount',
		'salary',
		'import',
	];

	public function childrens()
	{
		return $this->hasMany(COTechnicalStaffSalaryConcept::class,'parent');
	}

	public function father()
	{
		return $this->belongsTo(COTechnicalStaffSalaryConcept::class,'parent','id');
	}
}
