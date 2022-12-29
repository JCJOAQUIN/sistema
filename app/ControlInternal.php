<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ControlInternal extends Model
{
	public $timestamps = false;
	protected $fillable = 
	[
		'control_requisitions_id',
		'control_purchase_orders_id',
		'control_remittances_id',
		'control_banks_id',
		'control_docs_id',
		'state',
	];

	public function controlRequisition()
	{
		return $this->belongsTo(ControlRequisition::class, 'control_requisitions_id');
	}

	public function controlPurchaseOrder()
	{
		return $this->belongsTo(ControlPurchaseOrder::class, 'control_purchase_orders_id');
	}

	public function controlRemittance()
	{
		return $this->belongsTo(ControlRemittance::class, 'control_remittances_id');
	}

	public function controlBank()
	{
		return $this->belongsTo(ControlBank::class, 'control_banks_id');
	}

	public function controlDoc()
	{
		return $this->belongsTo(ControlDoc::class, 'control_docs_id');
	}
}
