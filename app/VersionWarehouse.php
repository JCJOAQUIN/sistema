<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VersionWarehouse extends Model
{
	public $timestamps  = false;
	protected $fillable = 
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
		'idWarehouse'
	];
	protected $whareHouseTypes = 
	[
		1 => 'Papelería',
		2 => 'Herramienta',
	];

	public function lot()
	{
		return $this->belongsTo(Lot::class,'idLot','idlot');
	}

	public function measurementD()
	{
		return $this->hasOne(CatMeasurementTypes::class,'id','measurement');
	}

	public function cat_c()
	{
		return $this->hasOne(CatWarehouseConcept::class,'id','concept');
	}

	public function location()
	{
		return $this->hasOne(Place::class,'id','place_location');
	}

	public function scopeOrderName($query)
	{
		return $query->orderBy('concept','asc');
	}

	public function wareHouse()
	{
		return $this->hasOne(CatWarehouseType::class,'id','warehouseType');
	}
}
