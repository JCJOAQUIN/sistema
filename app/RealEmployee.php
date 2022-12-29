<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RealEmployee extends Model
{
	public $timestamps  = true;
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
		'state_id',
		'sys_user',
		'doc_birth_certificate',
		'doc_proof_of_address',
		'doc_nss',
		'doc_ine',
		'doc_curp',
		'doc_rfc',
		'doc_cv',
		'doc_proof_of_studies',
		'doc_professional_license',
	];

	public function workerData()
	{
		return $this->hasMany(WorkerData::class,'idEmployee','id');
	}

	public function workerDataForEnterprise($id)
	{
		return $this->hasMany(WorkerData::class,'idEmployee','id')
			->where('enterprise',$id)
			->orderBy('worker_datas.id','desc');
	}

	public function workerDataVisible()
	{
		return $this->hasMany(WorkerData::class,'idEmployee','id')
			->where('visible',1);
	}

	public function bankData()
	{
		return $this->hasMany(EmployeeAccount::class,'idEmployee','id');
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

	public function orderedName()
	{
		return $this->last_name.' '.$this->scnd_last_name.' '.$this->name;
	}

	public function taxRegime()
	{
		return $this->hasOne(CatTaxRegime::class,'taxRegime','tax_regime');
	}

	public function documents()
	{
		return $this->hasMany(RealEmployeeDocument::class);
	}

	public function employeeFaceEnrollment()
	{
		return $this->hasOne(EmployeeFaceEnrollment::class,'employee_id','id');
	}
}
