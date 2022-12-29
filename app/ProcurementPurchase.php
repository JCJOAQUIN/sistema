<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcurementPurchase extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'account',
		'numberOrder',
		'numberCO',
		'descriptionShort',
		'status',
		'date_request',
		'date_obra',
		'date_promise',
		'date_close',
		'destination',
		'site',
		'code_wbs',
		'type_currency',
		'descriptionLong',
		'provider',
		'ubicationProvider',
		'contactProvider',
		'phoneProvider',
		'emailProvider',
		'total_request',
		'enterprise_id',
		'project_id',
		'idElaborate',
		'idKind',
		'buyer',
		'expeditor',
		'engineer',
		'contract',
		'full_load_warehouse',
	];
	protected $casts = 
	[
		'date_request' => 'datetime:Y-m-d',
		'date_request' => 'datetime:Y-m-d',
		'date_obra'    => 'datetime:Y-m-d',
		'date_promise' => 'datetime:Y-m-d',
		'date_close'   => 'datetime:Y-m-d',
	];

	public function details()
	{
		return $this->hasMany(ProcurementPurchaseDetail::class,'idprocurementPurchase','id');
	}

	public function documents()
	{
		return $this->hasMany(ProcurementPurchaseDocuments::class,'idprocurementPurchase','id');
	}

	public function remarks()
	{
		return $this->hasMany(ProcurementPurchaseRemarks::class,'idprocurementPurchase','id');
	}

	public function accountData()
	{
		return $this->hasOne(CatAccounts::class,'id','account');
	}

	public function wbs()
	{
		return $this->hasOne(CatCodeWBS::class,'id','code_wbs');
	}

	public function project()
	{
		return $this->hasOne(Project::class,'idproyect','project_id');
	}

	public function enterprise()
	{
		return $this->hasOne(Enterprise::class,'id','enterprise_id');
	}

	public function statusRequest()
	{
		return $this->hasOne(StatusRequest::class,'idrequestStatus','status');
	}

	public function history()
	{
		return $this->hasMany(ProcurementHistory::class,'folio','id');
	}

	public function warehouseStatus()
	{
		return $this->full_load_warehouse == 0 ? 'Pendiente' : 'Cargado';
	}

	public function milestones()
	{
		return $this->hasMany(ProcurementMilestone::class,'idprocurementPurchase','id');
	}
}
