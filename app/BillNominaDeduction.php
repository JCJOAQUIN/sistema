<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillNominaDeduction extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'type',
		'deductionKey',
		'concept',
		'amount',
		'bill_nomina_id',
	];

	public function deduction()
	{
		return $this->hasOne(CatDeduction::class,'id','type')
			->withoutGlobalScopes();
	}
}