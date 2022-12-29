<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillNominaPerception extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'type',
		'perceptionKey',
		'concept',
		'taxedAmount',
		'exemptAmount',
		'bill_nomina_id',
	];

	public function perception()
	{
		return $this->hasOne(CatPerception::class,'id','type')
			->withoutGlobalScopes();
	}
}