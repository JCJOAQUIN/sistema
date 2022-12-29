<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Nomina extends Model
{
	public $timestamps		= false;
	protected $primaryKey	= 'idnomina';
	protected $fillable		= 
	[
		'idnomina',
		'title',
		'datetitle',
		'from_date',
		'to_date',
		'amount',
		'ptu_to_pay',
		'idFolio',
		'idKind',
		'idCatPeriodicity',
		'idCatTypePayroll',
	];

	public function requestModel()
	{
		return $this->hasOne(RequestModel::class,'folio','idFolio');
	}

	public function nominaEmployee()
	{
		return $this->hasMany(NominaEmployee::class,'idnomina','idnomina');
	}

	public function typePayroll()
	{
		return $this->hasOne(CatTypePayroll::class,'id','idCatTypePayroll');
	}

	public function typeNomina()
	{
		switch ($this->type_nomina) 
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

	public function documents()
	{
		return $this->hasMany(NominaDocuments::class,'idNomina','idnomina');
	}
}
