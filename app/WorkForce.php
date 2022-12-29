<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkForce extends Model
{
	protected $fillable = 
	[
		'project_id',
		'wbs_id',
		'location',
		'description',
		'provider',
		'work_force',
		'total_workers',
		'man_hours_per_day',
		'date',
		'user_id',
	];
	public function projectData()
	{
		return $this->hasOne(Project::class,'idproyect','project_id');
	}

	public function wbsData()
	{
		return $this->hasOne(CatCodeWBS::class,'id','wbs_id');
	}
}
