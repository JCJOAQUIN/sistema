<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatCodeEDT extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'edt_number',
		'code',
		'description',
		'phase',
		'codewbs_id',
	];

	public function fullName()
	{
		return $this->code.' ('.$this->description.')';
	}
}
