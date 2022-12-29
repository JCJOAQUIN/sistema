<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequestModel extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'folio';
	protected $fillable   = 
	[
		'kind',
		'fDate',
		'reviewDate',
		'authorizeDate',
		'taxPayment',
		'PaymentDate',
		'deliveryDate',
		'status',
		'account',
		'idEnterprise',
		'idArea',
		'idDepartment',
		'idEnterpriseR',
		'idAreaR',
		'idDepartamentR',
		'idAccAcc',
		'idRequest',
		'idElaborate',
		'idCheck',
		'idAuthorize',
		'checkComment',
		'authorizeComment',
		'idProject',
		'idProjectR',
		'payment',
		'code',
		'free',
		'payDate',
		'paymentComment',
		'idprenomina',
		'idCheckConstruction',
		'reviewDateConstruction',
		'idWarehouseType',
		'remittance',
		'idRequisition',
		'idNomina',
		'statusWarehouse',
		'new_folio',
		'goToWarehouse',
		'code_edt',
		'code_wbs',
	];

	protected $dates = [
		'fDate',
		'reviewDate',
		'authorizeDate',
		'PaymentDate',
		'deliveryDate',
		'reviewDateConstruction'
	];

	public function request_has_reclassification()
	{
		return $this->hasMany(RequestHasReclassification::class,'folio','folio');
	}

	public function income()
	{
		return $this->hasMany(Income::class,'idFolio','folio');
	}

	public function bill()
	{
		return $this->hasMany(Bill::class,'folioRequest','folio');
	}

	public function billNF()
	{
		return $this->hasMany(NonFiscalBill::class,'folio','folio');
	}

	public function purchases()
	{
		return $this->hasMany(Purchase::class,'idFolio','folio');
	}

	public function purchaseRecord()
	{
		return $this->hasOne(PurchaseRecord::class,'idFolio','folio');
	}

	public function nominas()
	{
		return $this->hasMany(NominaApplication::class,'idFolio','folio');
	}

	public function nominasReal()
	{
		return $this->hasMany(Nomina::class,'idFolio','folio');
	}

	public function resource()
	{
		return $this->hasMany(Resource::class,'idFolio','folio');
	}

	public function stationery()
	{
		return $this->hasMany(Stationery::class,'idFolio','folio');
	}

	public function refunds()
	{
		return $this->hasMany(Refund::class,'idFolio','folio');
	}

	public function expenses()
	{
		return $this->hasMany(Expenses::class,'idFolio','folio');
	}

	public function staff()
	{
		return $this->hasMany(Staff::class,'idFolio','folio');
	}

	public function loan()
	{
		return $this->hasMany(Loan::class,'idFolio','folio');
	}

	public function requestUser()
	{
		return $this->hasOne(User::class,'id','idRequest');
	}

	public function elaborateUser()
	{
		return $this->hasOne(User::class,'id','idElaborate');
	}

	public function requestEnterprise()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterprise');
	}

	public function requestDepartment()
	{
		return $this->hasOne(Department::class,'id','idDepartment');
	}

	public function requestDirection()
	{
		return $this->hasOne(Area::class,'id','idArea');
	}

	public function requestProject()
	{
		return $this->hasOne(Project::class,'idproyect','idProject');
	}

	public function reviewedUser()
	{
		return $this->hasOne(User::class,'id','idCheck');
	}

	public function constructionReviewedUser()
	{
		return $this->hasOne(User::class,'id','idCheckConstruction');
	}

	public function enterprise()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterprise');
	}

	public function reviewedEnterprise()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterpriseR');
	}

	public function reviewedDepartment()
	{
		return $this->hasOne(Department::class,'id','idDepartamentR');
	}

	public function reviewedDirection()
	{
		return $this->hasOne(Area::class,'id','idAreaR');
	}

	public function reviewedProject()
	{
		return $this->hasOne(Project::class,'idproyect','idProjectR');
	}

	public function authorizedUser()
	{
		return $this->hasOne(User::class,'id','idAuthorize');
	}

	public function labels()
	{
		return $this->belongsToMany(Label::class,'request_has_labels','request_folio','labels_idlabels')
			->withPivot('request_kind');
	}

	public function labelsReport()
	{
		return $this->belongsToMany(Label::class,'request_has_labels','request_folio','labels_idlabels','folio','idlabels');
	}

	public function computer()
	{
		return $this->hasMany(Computer::class, 'idFolio', 'folio');
	}

	public function accounts()
	{
		return $this->belongsTo(Account::class,'account','idAccAcc');
	}

	public function accountsReview()
	{
		return $this->belongsTo(Account::class,'accountR','idAccAcc');
	}

	public function requestkind()
	{
		return $this->belongsTo(RequestKind::class,'kind','idrequestkind');
	}

	public function statusrequest()
	{
		return $this->hasOne(StatusRequest::class,'idrequestStatus','status');
	}

	public function payments()
	{
		return $this->belongsTo(Payment::class,'folio','idFolio');
	}

	public function paymentsRequest()
	{
		return $this->hasMany(Payment::class,'idFolio','folio');
	}

	public function adjustment()
	{
		return $this->hasMany(Adjustment::class, 'idFolio', 'folio');
	}

	public function loanEnterprise()
	{
		return $this->hasMany(LoanEnterprise::class, 'idFolio', 'folio');
	}

	public function purchaseEnterprise()
	{
		return $this->hasMany(PurchaseEnterprise::class,'idFolio','folio');
	}

	public function groups()
	{
		return $this->hasMany(Groups::class,'idFolio','folio');
	}

	public function movementsEnterprise()
	{
		return $this->hasMany(MovementsEnterprise::class,'idFolio','folio');
	}

	public function finance()
	{
		return $this->hasOne(Finance::class,'idFolio','folio');
	}

	public function wareHouse()
	{
		return $this->hasOne(CatWarehouseType::class,'id','idWarehouseType');
	}

	public function requisition()
	{
		return $this->hasOne(Requisition::class,'idFolio','folio');
	}

	public function budget()
	{
		return $this->hasOne(Budget::class,'request_id','folio');
	}

	public function requestRequisition()
	{
		return $this->hasMany(RequestModel::class,'idRequisition','folio')->whereIn('request_models.kind',[1,9]);
	}

	public function fromRequisition()
	{
		return $this->belongsTo(RequestModel::class,'idRequisition','folio');
	}

	public function procurementPurchase()
	{
		return $this->hasOne(ProcurementPurchase::class,'idFolio','folio');
	}

	public function workOrder()
	{
		return $this->hasOne(WorkOrder::class,'idFolio','folio');
	}

	public function history()
	{
		return $this->hasMany(ProcurementHistory::class,'folio','folio');
	}

	public function otherIncome()
	{
		return $this->hasOne(OtherIncome::class,'idFolio','folio');
	}

	public function childrens()
	{
		return $this->hasMany(RequestHasRequest::class,'folio','folio');
	}

	public function parent()
	{
		return $this->hasOne(RequestHasRequest::class,'children','folio');
	}

	public function wbs()
	{
		return $this->hasOne(CatCodeWBS::class,'id','code_wbs');
	}
	
	public function edt()
	{
		return $this->hasOne(CatCodeEDT::class,'id','code_edt');
	}

	public function flightsLodging()
	{
		return $this->hasOne(FlightLodging::class,'folio_request','folio');
	}

	public function nomina()
	{
		return $this->hasOne(Nomina::class,'idFolio','folio');
	}

	public function projectIncome()
	{
		switch ($this->kind) 
		{
			case '13':
				return $this->hasOne(PurchaseEnterprise::class,'idFolio','folio');
				break;
			
			case '14':
				return $this->hasOne(Groups::class,'idFolio','folio');
				break;

			default:
				return '';
				break;
		}
	}

	public function nominaData()
	{
		return $this->hasOne(Nomina::class,'idFolio','idNomina');
	}

	public function prenominaData()
	{
		return $this->hasOne(Prenomina::class,'idprenomina','idprenomina');
	}

}
