<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NonFiscalBillDetail extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idBillDetail';
	protected $fillable   = 
		[
			'idBillDetail',
			'quantity',
			'description',
			'value',
			'amount',
			'discount',
			'idBill',
		];
}
