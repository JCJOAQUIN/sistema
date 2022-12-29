<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailStationery extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idStatDetail';
	protected $fillable   = 
	[
		'quantity',
		'product',
		'short_code',
		'long_code',
		'commentaries',
		'idStat',
		'idwarehouse',
		'unitPrice',
		'subtotal',
		'iva',
		'total',
		'idDetailPurchase',
		'category',
		'deliveryDate',
	];

	public function stat()
	{
		return $this->belongsTo(Stationery::class,'idStat','idStationery');
	}

	public function labels()
	{
		return $this->hasMany(LabelDetailStationery::class,'idStatDetail','idStatDetail');
	}

	public function productDelivery()
	{
		return $this->hasOne(Warehouse::class,'idwarehouse','idwarehouse');
	}

	public function categoryData()
	{
		return $this->hasOne(CatWarehouseType::class,'id','category');
	}
}
