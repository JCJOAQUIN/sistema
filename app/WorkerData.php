<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkerData extends Model
{
	protected $fillable = 
	[
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
		'visible',
		'recorder',
		'status_imss',
		'wbs_id',
		'immediate_boss',
		'position_immediate_boss',
        'viatics',
        'camping',
	];

	protected $dates = 
	[
		'admissionDate',
		'admissionDateOld',
		'imssDate',
		'downDate',
		'endingDate',
		'reentryDate',
	];

	protected $casts = 
	[
		'admissionDate'		=> 'date:Y-m-d',
		'admissionDateOld'	=> 'date:Y-m-d',
		'imssDate'			=> 'date:Y-m-d',
		'downDate'			=> 'date:Y-m-d',
		'endingDate'		=> 'date:Y-m-d',
		'reentryDate'		=> 'date:Y-m-d',
	];

	public function places()
	{
		return $this->belongsToMany(Place::class,'worker_data_places','idWorkingData','idPlace');
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

	public function states()
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

	public function editor()
	{
		return $this->hasOne(User::class,'id','recorder');		
	}

	public function wbs()
	{
		return $this->hasOne(CatCodeWBS::class,'id','wbs_id');
	}

	public function employeeHasSubdepartment()
	{
		return $this->belongsToMany(Subdepartment::class,'employee_subdepartments','working_data_id','subdepartment_id');
	}

	public function employeeHasWbs()
	{
		return $this->belongsToMany(CatCodeWBS::class,'employee_w_b_s','working_data_id','cat_code_w_bs_id');
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

	public function employeeRegisterData()
	{
		return $this->hasOne(EmployerRegister::class,'employer_register','employer_register');
	}
}
