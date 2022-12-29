<?php

namespace App;

use Auth;
use Illuminate\Database\Eloquent\Model;

class RequisitionDetail extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idRequisition',
		'category',
		'part',
		'quantity',
		'unit',
		'description',
		'exists_warehouse',
		'idRequisitionHasProvider',
	];

	public function votingProvider()
	{
		return $this->hasMany(VotingProvider::class,'idRequisitionDetail','id');
	}

	public function getWinnerProvider()
	{
		return $this->hasMany(VotingProvider::class,'idRequisitionDetail','id')
			->leftJoin('requisition_has_providers','requisition_has_providers.id','voting_providers.idRequisitionHasProvider')
			->leftJoin('provider_secondaries','requisition_has_providers.idProviderSecondary','provider_secondaries.id')
			->select('requisition_has_providers.id as idRequisitionHasProvider','provider_secondaries.id as idProviderSecondary','provider_secondaries.businessName')
			->distinct();
	}

	public function priceWin($id)
	{
		return $this->hasOne(ProviderSecondaryPrice::class,'idRequisitionDetail','id')
			->where('idRequisitionHasProvider',$id);
	}

	public function categoryData()
	{
		return $this->hasOne(CatWarehouseType::class,'id','category');
	}

	public function getCategoriaAttribute()
	{
		$c = CatWarehouseType::where('id',$this->category)->first();
		return $c ? $c->description :'Sin categoría' ;
	}

	public function catNames()
	{
		return $this->hasOne(CatRequisitionName::class,'id','name');
	}

	public function procurementMaterialType()
	{
		return $this->hasOne(CatProcurementMaterial::class,'id','cat_procurement_material_id');
	}
}
