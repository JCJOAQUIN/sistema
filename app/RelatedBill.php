<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RelatedBill extends Model
{
    public $timestamps  = false;
	protected $fillable = 
	[
		'idBill',
		'idRelated',
		'partial',
        'prevBalance',
        'amount',
        'unpaidBalance',
        'cat_tax_object_id',
        'cat_relation_id',
	];

    public function cfdi()
	{
		return $this->hasOne(Bill::class,'idBill','idRelated');
	}

	public function taxes()
	{
		return $this->hasMany(BillTaxes::class,'related_bill_id');
	}

	public function taxesRet()
	{
		return $this->hasMany(BillTaxes::class,'related_bill_id')->where('type','Retención');
	}

	public function taxesRetIVA()
	{
		return $this->hasMany(BillTaxes::class,'related_bill_id')
			->where('type','Retención')
			->where('tax','002');
	}

	public function taxesRetISR()
	{
		return $this->hasMany(BillTaxes::class,'related_bill_id')
			->where('type','Retención')
			->where('tax','001');
	}

	public function taxesRetIEPS()
	{
		return $this->hasMany(BillTaxes::class,'related_bill_id')
			->where('type','Retención')
			->where('tax','003');
	}

	public function taxesTras()
	{
		return $this->hasMany(BillTaxes::class,'related_bill_id')->where('type','Traslado');
	}

	public function taxesTrasIVA16()
	{
		return $this->hasMany(BillTaxes::class,'related_bill_id')
			->where('type','Traslado')
			->where('tax','002')
			->where('quotaValue',0.16);
	}

	public function taxesTrasIVA8()
	{
		return $this->hasMany(BillTaxes::class,'related_bill_id')
			->where('type','Traslado')
			->where('tax','002')
			->where('quotaValue',0.08);
	}

	public function taxesTrasIVA0()
	{
		return $this->hasMany(BillTaxes::class,'related_bill_id')
			->where('type','Traslado')
			->where('tax','002')
			->where('quotaValue',0);
	}

	public function taxesTrasIVAExento()
	{
		return $this->hasMany(BillTaxes::class,'related_bill_id')
			->where('type','Traslado')
			->where('tax','002')
			->where('quota','Exento');
	}

	public function relationKind()
	{
		return $this->hasOne(CatRelation::class,'typeRelation','cat_relation_id')
			->withoutGlobalScopes();
	}
}
