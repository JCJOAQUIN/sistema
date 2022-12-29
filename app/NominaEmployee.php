<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NominaEmployee extends Model
{
	public $timestamps		= false;
	protected $primaryKey	= 'idnominaEmployee';
	protected $fillable		= 
	[
		'idnominaEmployee',
		'idrealEmployee',
		'idworkingData',
		'type',
		'fiscal',
		'idnomina',
		'visible',
		'from_date',
		'to_date',
		'idCatPeriodicity',
		'absence',
		'loan_retention',
		'loan_perception',
		'day_bonus',
		'worked_days',
		'payment',
		'down_date',
	];

	public function employee()
	{
		return $this->hasMany(RealEmployee::class,'id','idrealEmployee');
	}

	public function workerData()
	{
		return $this->hasMany(WorkerData::class,'id','idworkingData');
	}

	public function nominasEmployeeNF()
	{
		return $this->hasMany(NominaEmployeeNF::class,'idnominaEmployee','idnominaEmployee');
	}

	public function nominasEmployeeF()
	{
		return $this->hasMany(NominaEmployeeF::class,'idnominaEmployee','idnominaEmployee');
	}

	public function liquidation()
	{
		return $this->hasMany(Liquidation::class,'idnominaEmployee','idnominaEmployee');
	}

	public function bonus()
	{
		return $this->hasMany(Bonus::class,'idnominaEmployee','idnominaEmployee');
	}

	public function settlement()
	{
		return $this->hasMany(Settlement::class,'idnominaEmployee','idnominaEmployee');
	}

	public function vacationPremium()
	{
		return $this->hasMany(VacationPremium::class,'idnominaEmployee','idnominaEmployee');
	}

	public function salary()
	{
		return $this->hasMany(Salary::class,'idnominaEmployee','idnominaEmployee');
	}

	public function profitSharing()
	{
		return $this->hasMany(ProfitSharing::class,'idnominaEmployee','idnominaEmployee');
	}

	public function nominaCFDI()
	{
		return $this->belongsToMany(Bill::class,'employee_bill','idNominaEmployee','idBill','idnominaEmployee','idBill');
	}

	public function billed()
	{
		return $this->belongsToMany(Bill::class,'employee_bill','idNominaEmployee','idBill','idnominaEmployee','idBill')->where('bills.status',1);
	}

	public function pendingBilling()
	{
		return $this->belongsToMany(Bill::class,'employee_bill','idNominaEmployee','idBill','idnominaEmployee','idBill')->where('bills.status',0);
	}

	public function payments()
	{
		return $this->hasMany(Payment::class,'idnominaEmployee','idnominaEmployee');
	}

	public function typeNomina()
	{
		switch ($this->fiscal) 
		{
			case 1:
				return 'Fiscal';
				break;
			case 2:
				return 'No Fiscal';
				break;
			case 3:
				return 'Nom35';
				break;
			default:
				return 'Sin asignación';
				break;
		}
	}

	public function category()
	{
		switch ($this->type)
		{
			case 1:
				return 'Obra';
				break;

			case 2:
				return 'Administrativa';
				break;
		}
	}

	public function documentsNom35()
	{
		return $this->hasMany(NominaDocuments::class,'idnominaEmployee','idnominaEmployee');
	}

	public function nomina()
	{
		return $this->belongsTo(Nomina::class,'idnomina','idnomina');
	}

	public function periodicityData()
	{
		return $this->hasOne(CatPeriodicity::class,'c_periodicity','idCatPeriodicity');
	}

	public function getWorkerData()
	{
		return $this->hasOne(WorkerData::class,'id','idworkingData');
	}
}
