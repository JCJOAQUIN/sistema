<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ControlPurchaseOrder extends Model
{
	public $timestamps = false;
	protected $fillable = 
	[
		'data',
		'number',
		'provider',
	];

	public function controlInternal()
	{
		return $this->hasOne(ControlInternal::class, 'control_purchase_orders_id');
	}

}
