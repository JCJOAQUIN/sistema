<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillNominaOtherPayment extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'type',
		'otherPaymentKey',
		'concept',
		'amount',
		'bill_nomina_id',
		'subsidy_caused',
	];

	public function otherPayment()
	{
		return $this->hasOne(CatOtherPayment::class,'id','type')
			->withoutGlobalScopes();
	}
}