<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BudgetDetails extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'code',
		'concept',
		'measurement',
		'date',
		'amount',
		'price',
		'import',
		'incidence',
		'father'
	];

	public function childrens()
	{
		return $this->hasMany(BudgetDetails::class,'father');
	}

	public function parent()
	{
		return $this->belongsTo(BudgetDetails::class,'father','id');
	}
}
