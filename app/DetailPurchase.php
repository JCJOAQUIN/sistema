<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailPurchase extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idDetailPurchase';
	protected $fillable   = 
	[
		'idPurchase',
		'quantity',
		'description',
		'unitPrice',
		'tax',
		'amount',
		'statusWarehouse',
		'commentaries',
	];

	public function purchase()
	{
		return $this->belongsTo(Purchase::class,'idPurchase','idPurchase');
	}

	public function labels()
	{
		return $this->hasMany(LabelDetailPurchase::class,'idDetailPurchase','idDetailPurchase');
	}

	public function labelsReport()
	{
		return $this->belongsToMany(Label::class,'label_detail_purchases','idDetailPurchase','idlabels','idDetailPurchase','idlabels');
	}

	public function taxes()
	{
		return $this->hasMany(TaxesPurchase::class,'idDetailPurchase','idDetailPurchase');
	}

	public function retentions()
	{
		return $this->hasMany(RetentionPurchase::class,'idDetailPurchase','idDetailPurchase');
	}

	public function statDetail()
	{
		return $this->hasOne(DetailStationery::class,'idDetailPurchase','idDetailPurchase');
	}

	public function computerDetail()
	{
		return $this->hasOne(Computer::class,'idDetailPurchase','idDetailPurchase');
	}

	public function getEstatusAlmacenAttribute()
	{
		return $this->statusWarehouse == 0 ? 'Pendiente' : 'Si';
	}

	public function getCategoriaAttribute()
	{
		$c = CatWarehouseType::where('id',$this->category)->first();
		return $c ? $c->description :'Sin categoría';
	}
}
