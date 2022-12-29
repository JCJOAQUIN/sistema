<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillTaxes extends Model
{
	protected $primaryKey = 'idBillTaxes';
	public $timestamps    = false;
	protected $fillable   = 
	[
		'idBillTaxes',
		'base',
		'quota',
		'quotaValue',
		'amount',
		'tax',
		'type',
		'idBillDetail',
		'related_bill_id',
	];

	public function cfdiTax()
	{
		return $this->hasOne(CatTaxes::class,'tax','tax')
			->withoutGlobalScopes();
	}
}
