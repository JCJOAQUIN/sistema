<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Prenomina extends Model
{
	protected $primaryKey = 'idprenomina';
	protected $fillable   = 
	[
		'idprenomina',
		'title',
		'datetitle',
		'idCatTypePayroll',
		'status',
		'kind',
		'date',
		'project_id',
		'user_id',
		'updated_at',
		'employer_register'
	];

	const CREATED_AT	= 'date';

	public function typePayroll()
	{
		return $this->hasOne(CatTypePayroll::class,'id','idCatTypePayroll');
	}

	public function employee()
	{
		return $this->belongsToMany(RealEmployee::class,'prenomina_employee','idprenomina','idreal_employee','idprenomina','id');
	}

	public function employeeData()
	{
		return $this->hasMany(PrenominaEmployee::class,'idprenomina','idprenomina');
	}

	public function employeeRegisterData()
	{
		return $this->hasOne(EmployerRegister::class,'employer_register','employer_register');
	}

	/*
		public function employeeObra()
		{
			return $this->belongsToMany(RealEmployee::class,'prenomina_employee','idprenomina','idreal_employee','absence','extra_hours','holidays','sundays','idprenomina','id');
		}
	*/
}
