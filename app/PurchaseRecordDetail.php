<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseRecordDetail extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idPurchaseRecord',
		'quantity',
		'unit',
		'description',
		'unitPrice',
		'tax',
		'discount',
		'subtotal',
		'total',
		'typeTax',
	];

	public function labels()
	{
		return $this->hasMany(PurchaseRecordLabel::class,'idPurchaseRecordDetail','id');
	}

	public function taxes()
	{
		return $this->hasMany(PurchaseRecordTaxes::class,'idPurchaseRecordDetail','id');
	}

	public function retentions()
	{
		return $this->hasMany(PurchaseRecordRetention::class,'idPurchaseRecordDetail','id');
	}

	public function labelsReport()
	{
		return $this->belongsToMany(Label::class,'purchase_record_labels','idPurchaseRecordDetail','idLabel','id','idlabels');
	}
}
