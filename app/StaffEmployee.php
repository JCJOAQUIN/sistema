<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StaffEmployee extends Model
{
	protected $primaryKey = 'id';
	protected $fillable = 
	[
		'name',
		'last_name',
		'scnd_last_name',
		'curp',
		'rfc',
		'tax_regime',
		'imss',
		'street',
		'number',
		'colony',
		'cp',
		'city',
		'email',
		'state_id',
		
		'state',
		'project',
		'enterprise',
		'account',
		'place',
		'direction',
		'department',
		'position',
		'admissionDate',
		'imssDate',
		'downDate',
		'endingDate',
		'reentryDate',
		'workerType',
		'regime_id',
		'workerStatus',
		'status_reason',
		'sdi',
		'periodicity',
		'employer_register',
		'paymentWay',
		'netIncome',
		'complement',
		'fonacot',
		'nomina',
		'bono',
		'infonavitCredit',
		'infonavitDiscount',
		'infonavitDiscountType',
		'status_imss',
		'wbs_id',
		'immediate_boss',
		'position_immediate_boss',
		'viatics',
		'camping',
		'replace',
		'purpose',
		'requeriments',
		'observations',
		'subdepartment_id',
		'doc_birth_certificate',
		'doc_proof_of_address',
		'doc_nss',
		'doc_ine',
		'doc_curp',
		'doc_rfc',
		'doc_cv',
		'doc_proof_of_studies',
		'doc_professional_license',
		'qualified_employee',
		'staff_id',
		'version'
	];

	protected $dates = 
	[
		'admissionDate',
		'imssDate',
		'downDate',
		'endingDate',
		'reentryDate',
	];


	public function bankData()
	{
		return $this->hasMany(StaffAccounts::class,'id_staff_employee','id');
	}

	public function states()
	{
		return $this->hasOne(State::class,'idstate','state_id');
	}

	public function scopeOrderName($query)
	{
		return $query->orderBy('name','asc')->orderBy('last_name','asc')->orderBy('scnd_last_name','asc');
	}

	public function fullName()
	{
		return $this->name.' '.$this->last_name.' '.$this->scnd_last_name;
	}

	public function enterprises()
	{
		return $this->hasOne(Enterprise::class,'id','enterprise');
	}

	public function projects()
	{
		return $this->hasOne(Project::class,'idproyect','project');
	}

	public function directions()
	{
		return $this->hasOne(Area::class,'id','direction');
	}

	public function departments()
	{
		return $this->hasOne(Department::class,'id','department');
	}

	public function accounts()
	{
		return $this->hasOne(Account::class,'idAccAcc','account');
	}

	public function statesWork()
	{
		return $this->hasOne(State::class,'idstate','state');
	}

	public function worker()
	{
		return $this->hasOne(CatContractType::class,'id','workerType');
	}

	public function regime()
	{
		return $this->hasOne(CatRegimeType::class,'id','regime_id');
	}

	public function periodicities()
	{
		return $this->hasOne(CatPeriodicity::class,'c_periodicity','periodicity');
	}

	public function paymentMethod()
	{
		return $this->hasOne(PaymentMethod::class,'idpaymentMethod','paymentWay');
	}

	public function subdepartment()
	{
		return $this->hasOne(Subdepartment::class,'id','subdepartment_id');
	}

	public function statusImss()
	{
		switch ($this->status_imss) 
		{
			case 1:
				return 'Activo';
				break;

			case 0:
				return 'Inactivo';
				break;
			
			default:
				return 'Inactivo';
				break;
		}
	}

	public function workerStatus()
	{
		switch ($this->workerStatus) 
		{
			case 1:
				return 'Activo';
				break;
			case 2:
				return 'Baja pacial';
				break;
			case 3:
				return 'Baja definitiva';
				break;
			case 4:
				return 'Suspensión';
				break;
			case 5:
				return 'Boletinado';
				break;
			 default:
				return '';
				break;
		}
	}

	public function taxRegime()
	{
		return $this->hasOne(CatTaxRegime::class,'taxRegime','tax_regime');
	}

	public function computerRequired()
	{
		 switch ($this->computer_required) 
		{
			case 1:
				return 'Sí';
				break;
			
			 default:
				return 'No';
				break;
		}
	}

	public function qualifiedEmployee()
	{
		 switch ($this->qualified_employee) 
		{
			case 1:
				return 'Sí';
				break;
			
			 default:
				return 'No';
				break;
		}
	}

	public function wbsData()
	{
		return $this->hasOne(CatCodeWBS::class,'id','wbs_id');
	}

	public function staffDocuments()
	{
		return $this->hasMany(StaffDocuments::class, 'id_staff_employee', 'id');
	}

	public function staffAccounts()
	{
		return $this->hasMany(StaffAccounts::class, 'id_staff_employee', 'id');
	}
	
}
