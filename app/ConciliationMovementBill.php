<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConciliationMovementBill extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idbill',
		'idmovement',
		'type',
	];

	public function bills()
	{
		return $this->hasOne(Bill::class,'idBill','idbill');
	}

	public function billsNF()
	{
		return $this->hasOne(NonFiscalBill::class,'idBill','idNoFiscalBill');
	}

	public function movements()
	{
		return $this->hasOne(Movement::class,'idmovement','idmovement');
	}
}
