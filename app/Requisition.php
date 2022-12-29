<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idFolio',
		'idKind',
		'title',
		'datetile',
		'number',
	];

	public function details()
	{
		return $this->hasMany(RequisitionDetail::class,'idRequisition','id');
	}

	public function requisitionHasProvider()
	{
		return $this->hasMany(RequisitionHasProvider::class,'idRequisition','id');
	}

	public function votingProvider()
	{
		return $this->hasMany(VotingProvider::class,'idRequisition','id')->groupBy('user_id');
	}

	public function getWinnerProvider()
	{
		return $this->hasMany(VotingProvider::class,'idRequisition','id')
			->leftJoin('requisition_has_providers','requisition_has_providers.id','voting_providers.idRequisitionHasProvider')
			->leftJoin('provider_secondaries','requisition_has_providers.idProviderSecondary','provider_secondaries.id')
			->select('requisition_has_providers.id as idRequisitionHasProvider','provider_secondaries.id as idProviderSecondary','provider_secondaries.businessName','provider_secondaries.rfc')
			->distinct();
	}

	public function requests()
	{
		return $this->hasMany(RequestModel::class,'idRequisition','idFolio');
	}

	public function purchases()
	{
		return $this->hasMany(Purchase::class,'idRequisition','idFolio');
	}

	public function refunds()
	{
		return $this->hasMany(Refund::class,'idRequisition','idFolio');
	}

	public function getPrioridadAttribute()
	{
		return $this->urgent == 1 ? 'Alta' : 'Baja';
	}

	public function documents()
	{
		return $this->hasMany(RequisitionDocuments::class,'idRequisition','id');
	}

	public function wbs()
	{
		return $this->hasOne(CatCodeWBS::class,'id','code_wbs');
	}

	public function edt()
	{
		return $this->hasOne(CatCodeEDT::class,'id','code_edt');
	}

	public function typeRequisition()
	{
		return $this->hasOne(RequisitionType::class,'id','requisition_type');
	}

	public function staff()
	{
		return $this->hasOne(RequisitionStaff::class,'requisition_id','id');
	}

	public function staffResponsabilities()
	{
		return $this->hasMany(RequisitionStaffResponsibilities::class,'requisition_id','id');
	}

	public function staffDesirables()
	{
		return $this->hasMany(RequisitionStaffDesirables::class,'requisition_id','id');
	}

	public function staffFunctions()
	{
		return $this->hasMany(RequisitionStaffFunctions::class,'requisition_id','id');
	}

	public function employees()
	{
		return $this->hasMany(RequisitionEmployee::class);
	}

	public function request_model()
	{
		return $this->hasOne(RequestModel::class,'folio','idFolio');
	}
}
