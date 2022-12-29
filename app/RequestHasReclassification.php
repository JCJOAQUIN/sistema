<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequestHasReclassification extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'folio',
		'kind',
		'idEnterprise',
		'idDepartment',
		'idArea',
		'idProject',
		'idAccAcc',
		'idresourcedetail',
		'idRefundDetail',
		'idExpensesDetail',
		'idUser',
		'date',
		'commentaries',
	];

	public function user()
	{
		return $this->hasOne(User::class,'id','idUser');
	}

	public function enterprise()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterprise');
	}

	public function department()
	{
		return $this->hasOne(Department::class,'id','idDepartment');
	}

	public function direction()
	{
		return $this->hasOne(Area::class,'id','idArea');
	}

	public function project()
	{
		return $this->hasOne(Project::class,'idproyect','idProject');
	}

	public function accounts()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccAcc');
	}

	public function resource()
	{
		return $this->hasOne(ResourceDetail::class,'idresourcedetail','idresourcedetail');
	}

	public function refund()
	{
		return $this->hasOne(RefundDetail::class,'idRefundDetail','idRefundDetail');
	}

	public function expense()
	{
		return $this->hasOne(ExpensesDetail::class,'idExpensesDetail','idExpensesDetail');
	}

	public function enterpriseOrigin()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterpriseOrigin');
	}

	public function departmentOrigin()
	{
		return $this->hasOne(Department::class,'id','idDepartmentOrigin');
	}

	public function directionOrigin()
	{
		return $this->hasOne(Area::class,'id','idAreaOrigin');
	}

	public function projectOrigin()
	{
		return $this->hasOne(Project::class,'idproyect','idProjectOrigin');
	}

	public function accountsOrigin()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccAccOrigin');
	}

	public function enterpriseDestiny()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterpriseDestiny');
	}

	public function departmentDestiny()
	{
		return $this->hasOne(Department::class,'id','idDepartmentDestiny');
	}

	public function directionDestiny()
	{
		return $this->hasOne(Area::class,'id','idAreaDestiny');
	}

	public function projectDestiny()
	{
		return $this->hasOne(Project::class,'idproyect','idProjectDestiny');
	}

	public function accountsDestiny()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccAccDestiny');
	}

	public function wbs()
	{
		return $this->hasOne(CatCodeWBS::class,'id','code_wbs');
	}
	
	public function edt()
	{
		return $this->hasOne(CatCodeEDT::class,'id','code_edt');
	}
}
