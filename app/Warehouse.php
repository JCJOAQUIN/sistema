<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idwarehouse';
	protected $fillable   = 
	[
		'concept',
		'quantity',
		'quantityReal',
		'short_code',
		'long_code',
		'measurement',
		'commentaries',
		'amountUnit',
		'typeTax',
		'subtotal',
		'iva',
		'amount',
		'idLot',
		'warehouseType',
		'place_location',
		'status',
		'account',
		'type',
		'brand',
		'storage',
		'processor',
		'ram',
		'sku',
		'damaged',
	];
	protected $whareHouseTypes = 
	[
		1 => 'Papelería',
		2 => 'Herramienta',
	];

	public function lot()
	{
		return $this->belongsTo('App\Lot','idLot','idlot');
	}

	public function measurementD()
	{
		return $this->hasOne('App\CatMeasurementTypes','id','measurement');
	}

	public function cat_c()
	{
		return $this->hasOne('App\CatWarehouseConcept','id','concept');
	}

	public function location()
	{
		return $this->hasOne('App\Place','id','place_location');
	}

	public function scopeOrderName($query)
	{
		return $query->orderBy('concept','asc');
	}

	public function wareHouse()
	{
		return $this->hasOne('App\CatWarehouseType','id','warehouseType');
	}

	public function versions()
	{
		return $this->hasMany('App\VersionWarehouse','idWarehouse','idwarehouse');
	}

	public function accounts()
	{
		return $this->belongsTo('App\Account','account','idAccAcc');
	}
}
