<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ControlIncidents extends Model
{
	protected $fillable = 
	[
		'incident_number',
		'project_id',
		'wbs_id',
		'location_wbs',
		'real_employee_id',
		'date_incident',
		'impact_level',
		'status',
		'description',
		'causes',
		'recommendation',
		'communique',
		'user_id',
	];

	public function requestProject()
	{
		return $this->hasOne(Project::class,'idproyect','project_id');
	}
	public function wbs()
	{
		return $this->hasOne(CatCodeWBS::class,'id','wbs_id');
	}
	public function employeeData()
	{
		return $this->hasOne(RealEmployee::class,'id','real_employee_id');
	}

	public function documents()
	{
		return $this->hasMany(ControlIncidentDocument::class,'control_incident_id','id');
	}
}
