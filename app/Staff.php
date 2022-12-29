<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idStaff';
	protected $fillable   = 
	[
		'idFolio',
		'idKind',
		'boss',
		'schedule_start',
		'schedule_end',
		'minSalary',
		'maxSalary',
		'reason',
		'role_id',
		'position',
		'periodicity',
		'description',
		'habilities',
		'experience',
	];

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function functions()
	{
		return $this->hasMany(StaffFunction::class,'idStaff','idStaff');
	}

	public function desirable()
	{
		return $this->hasMany(StaffDesirable::class,'idStaff','idStaff');
	}

	public function responsibility()
	{
		return $this->belongsToMany(Responsibility::class,'staff_responsibilities','idStaff','idResponsibility');
	}

	public function staffEmployees()
	{
		return $this->hasMany(StaffEmployee::class, 'staff_id', 'idStaff');
	}
}
