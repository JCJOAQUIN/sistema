<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ControlRemittance extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'remittances',
		'data',
		'invoice',
		'invoice_amount',
		'credit_note',
		'subtotal',
		'discount',
		'IVA',
		'total',
	];

	public function controlInternal()
	{
		return $this->hasOne(ControlInternal::class, 'control_remittances_id');
	}
}
