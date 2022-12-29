<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COFinancialConcept extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'parent',
		'concept',
	];

	public function childrens()
	{
		return $this->hasMany(COFinancialConcept::class,'parent');
	}
	public function father()
	{
		return $this->belongsTo(COFinancialConcept::class,'parent','id');
	}
}
