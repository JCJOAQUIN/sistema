<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RefundDetail extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idRefundDetail';
	protected $fillable   = 
	[
		'idRefundDetail',
		'idRefund',
		'RefundDate',
		'taxPayment',
		'document',
		'concept',
		'amount',
		'tax',
		'sAmount',
		'idAccount',
		'idAccountR',
		'quantity',
		'category',
	];

	public function refund()
	{
		return $this->belongsTo(Refund::class,'idRefund','idRefund');
	}

	public function account()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccount');
	}

	public function accountR()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccountR');
	}

	public function labels()
	{
		return $this->hasMany(LabelDetailRefund::class,'idRefundDetail','idRefundDetail');
	}

	public function labelsReport()
	{
		return $this->belongsToMany(Label::class,'label_detail_refunds','idRefundDetail','idlabels','idRefundDetail','idlabels');
	}

	public function taxes()
	{
		return $this->hasMany(TaxesRefund::class,'idRefundDetail','idRefundDetail');
	}

	public function getCategoriaAttribute()
	{
		$c = CatWarehouseType::where('id',$this->category)->first();
		return $c ? $c->description :'Sin categoría' ;
	}

	public function refundDocuments()
	{
		return $this->hasMany(RefundDocuments::class,'idRefundDetail','idRefundDetail');
	}

    public function retentions()
    {
        return $this->hasMany('App\RefundRetentions','idRefundDetail','idRefundDetail');
    }
}
