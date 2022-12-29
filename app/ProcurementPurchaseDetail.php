<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcurementPurchaseDetail extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'part',
		'code',
		'unit',
		'description',
		'quantity',
		'price',
		'total_concept',
		'type_currency',
		'date_one',
		'date_two',
		'idprocurementPurchase',
		'warehouseStatus',
	];
	protected $casts = 
	[
		'date_one' => 'datetime:Y-m-d',
		'date_two' => 'datetime:Y-m-d',
	];

	public function warehouseStatus()
	{
		return $this->warehouseStatus == 0 ? 'Pendiente' : 'Cargado';
	}

	public function warehouseItem()
	{
		return $this->hasOne(ProcurementWarehouse::class,'idProcurementPurchaseDetail','id');
	}
}
