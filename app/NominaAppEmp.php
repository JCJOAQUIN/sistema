<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NominaAppEmp extends Model
{
	public $timestamps = false;
	protected $primaryKey = 'idNomAppEmp';
	protected $fillable =
	[
		'idNominaApplication',
		'idUsers',
		'bank',
		'account',
		'clabe',
		'cardNumber',
		'reference',
		'amount',
		'description',
		'idAccount',
		'idEnterprise',
		'idArea',
		'idDepartment',
		'idProject',
		'idpaymentMethod',
	];

	public function noApp()
	{
		return $this->belongsTo(NominaApplication::class,'idNominaApplication','idNominaApplication');
	}

	public function users()
	{
		return $this->hasMany(NominaAppEmp::class,'idUsers','id');
	}

	public function employee()
	{
		return $this->hasOne(User::class,'id','idUsers');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}

	public function enterprise()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterprise');
	}

	public function department()
	{
		return $this->hasOne(Department::class,'id','idDepartment');
	}

	public function area()
	{
		return $this->hasOne(Area::class,'id','idArea');
	}

	public function project()
	{
		return $this->hasOne(Project::class,'idproyect','idProject');
	}

	public function accounts()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccount');
	}
}
