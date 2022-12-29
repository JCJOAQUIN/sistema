<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseEnterpriseDetail extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idPurchaseEnterpriseDetail';
	protected $fillable   = 
	[
		'quantity',
		'unity',
		'description',
		'unitPrice',
		'tax',
		'typeTax',
		'subtotal',
		'amount',
		'idpurchaseEnterprise',
	];

	public function purchaseEnterprise()
	{
		return $this->belongsTo(PurchaseEnterprise::class,'idpurchaseEnterprise','idpurchaseEnterprise');
	}

	public function labels()
	{
		return $this->hasMany(PurchaseEnterpriseDetailLabel::class,'idPurchaseEnterpriseDetail','idPurchaseEnterpriseDetail');
	}

	public function labelsReport()
	{
		return $this->belongsToMany(Label::class,'purchase_enterprise_detail_labels','idPurchaseEnterpriseDetail','idlabels','idPurchaseEnterpriseDetail','idlabels');
	}

	public function taxes()
	{
		return $this->hasMany(PurchaseEnterpriseTaxes::class,'idPurchaseEnterpriseDetail','idPurchaseEnterpriseDetail');
	}

	public function retentions()
	{
		return $this->hasMany(PurchaseEnterpriseRetention::class,'idPurchaseEnterpriseDetail','idPurchaseEnterpriseDetail');
	}
}
