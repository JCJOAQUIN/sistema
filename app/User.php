<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPasswordPersonalize;

class User extends Authenticatable
{
	use Notifiable;

	protected $fillable = 
	[
		'name',
		'last_name',
		'scnd_last_name',
		'gender',
		'phone',
		'extension',
		'email',
		'status',
		'role_id',
		'area_id',
		'departament_id',
		'position',
		'cash',
		'cash_amount',
		'sys_user',
		'active',
		'notification',
	];
	protected $hidden = 
	[
		'password',
		'remember_token',
	];

	public function role()
	{
		return $this->belongsTo(Role::class,'role_id','id');
	}

	public function area()
	{
		return $this->belongsTo(Area::class,'area_id','id');
	}

	public function departament()
	{
		return $this->belongsTo(Department::class,'departament_id','id');
	}

	public function module()
	{
		return $this->belongsToMany(Module::class,'user_has_modules','user_id','module_id');
	}

	public function enterprise()
	{
		return $this->belongsToMany(Enterprise::class,'user_has_enterprise');
	}

	public function employee()
	{
		return $this->hasMany(Employee::class,'idUsers','id');
	}

	public function nomAppEmp()
	{
		return $this->belongsTo(NominaAppEmp::class,'idUsers','id');
	}

	public function loan()
	{
		return $this->belongsTo(Loan::class,'idUsers','id');
	}

	public function requestChecked()
	{
		return $this->hasOne(RequestModel::class,'idCheck','id');
	}

	public function computer()
	{
		return $this->hasMany(Computer::class, 'idUsers', 'id');
	}

	public function inReview()
	{
		return $this->belongsToMany(SectionTickets::class,'user_review_ticket');
	}

	public function inChargeProject($id)
	{
		return $this->belongsToMany(PermissionProject::class,'user_has_modules','user_id','iduser_has_module','id','user_has_module_iduser_has_module')
			->withPivot('module_id')
			->where('module_id',$id);
	}

	public function inChargeDep($id)
	{
		return $this->belongsToMany(PermissionDep::class,'user_has_modules','user_id','iduser_has_module','id','user_has_module_iduser_has_module')
			->withPivot('module_id')
			->where('module_id',$id);
	}

	public function inChargeDepGet()
	{
		return $this->belongsToMany(PermissionDep::class,'user_has_modules','user_id','iduser_has_module','id','user_has_module_iduser_has_module')
			->withPivot('module_id');
	}

	public function inChargeEnt($id)
	{
		return $this->belongsToMany(PermissionEnt::class,'user_has_modules','user_id','iduser_has_module','id','user_has_module_iduser_has_module')
			->withPivot('module_id')
			->where('module_id',$id);
	}

	public function inChargeEntGet()
	{
		return $this->belongsToMany(PermissionEnt::class,'user_has_modules','user_id','iduser_has_module','id','user_has_module_iduser_has_module')
			->withPivot('module_id');
	}

	public function inChargeReq($id)
	{
		return $this->belongsToMany(PermissionReq::class,'user_has_modules','user_id','iduser_has_module','id','user_has_module_id')
			->withPivot('module_id')
			->where('module_id',$id);
	}

	public function inChargeReqGet()
	{
		return $this->belongsToMany(PermissionReq::class,'user_has_modules','user_id','iduser_has_module','id','user_has_module_id')
			->withPivot('module_id');
	}

	public function canUploadFiles($id)
	{
		return $this->belongsToMany(PermissionUploadFile::class,'user_has_modules','user_id','iduser_has_module','id','user_has_module_id')
			->withPivot('module_id')
			->where('module_id',$id);
	}

	public function canUploadFilesGet()
	{
		return $this->belongsToMany(PermissionUploadFile::class,'user_has_modules','user_id','iduser_has_module','id','user_has_module_id')
			->withPivot('module_id');
	}

	public function notifications()
	{
		return $this->hasMany(Notifications::class,'user_id','id');
	}

	public function employeeData()
	{
		return $this->hasOne(RealEmployee::class,'id','real_employee_id');
	}

	public function requested()
	{
		return $this->hasMany(RequestModel::class,'idRequest','id');
	}

	public function sendPasswordResetNotification($token)
	{
		$this->notify(new ResetPasswordPersonalize($token));
	}

	public function scopeOrderName($query)
	{
		return $query->orderBy('name','asc')->orderBy('last_name','asc')->orderBy('scnd_last_name','asc');
	}

	public function fullName()
	{
		return $this->name . ' ' . $this->last_name . ' ' . $this->scnd_last_name;
	}

	public function globalCheck()
	{
		return $this->hasMany('App\User_has_module');
	}

	public function moduleReq($id)
	{
		return $this->belongsToMany('App\Module','user_has_module','user_id','module_id')
				->where('module_id',$id);
	}

	public function inChargeProjectGet()
	{
		return $this->belongsToMany(PermissionProject::class,'user_has_modules','user_id','iduser_has_module','id','user_has_module_iduser_has_module')
			->withPivot('module_id');
	}
}
