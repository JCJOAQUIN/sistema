<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdjustmentDetail extends Model
{
	protected $primaryKey = 'idadjustmentDetail';
	public $timestamps    = false;
	protected $fillable   = 
	[
		'quantity',
		'unit',
		'description',
		'unitPrice',
		'tax',
		'typeTax',
		'subtotal',
		'amount',
		'idadjustment',
	];

	public function adjustment()
	{
		return $this->belongsTo(Adjustment::class,'idadjustment','idadjustment');
	}

	public function labels()
	{
		return $this->hasMany(AdjustmentDetailLabel::class,'idadjustmentDetail','idadjustmentDetail');
	}

	public function labelsReport()
	{
		return $this->belongsToMany(Label::class,'labelDetailAdjustment','idadjustmentDetail','idlabels','idadjustmentDetail','idlabels');
	}

	public function taxes()
	{
		return $this->hasMany(AdjustmentTaxes::class,'idadjustmentDetail','idadjustmentDetail');
	}

	public function retentions()
	{
		return $this->hasMany(AdjustmentRetention::class,'idadjustmentDetail','idadjustmentDetail');
	}
}
