<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdministrativeBudget extends Model
{
	protected $fillable =
	[
		'enterprise_id',
		'department_id',
		'project_id',
		'periodicity',
		'initRange',
		'endRange',
		'weekOfYear',
		'year',
		'path',
		'alert_percent',
		'users_id',
	];

	public function enterprise()
	{
		return $this->hasOne(Enterprise::class,'id','enterprise_id');
	}

	public function project()
	{
		return $this->hasOne(Project::class,'idproyect','project_id');
	}

	public function department()
	{
		return $this->hasOne(Department::class,'id','department_id');
	}

	public function detail()
	{
		return $this->hasMany(AdministrativeBudgetDetail::class,'idAdministrativeBudget','id');
	}

	public function user()
	{
		return $this->hasOne(User::class,'id','users_id');
	}

	public function periodicityData()
	{
		return $this->periodicity== 1 ? 'Semanal' : 'Mensual';
	}
}
