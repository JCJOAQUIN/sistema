<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activities extends Model
{
	protected $fillable = [
		'project_id',
		'wbs_id',
		'folio',
		'contractor',
		'specialty',
		'start_date',
		'start_hour',
		'end_date',
		'end_hour',
		'area',
		'personal_number',
		'resource_code',
		'status_code',
		'causes_code',
		'description',
		'user_id',
	];

	public function project()
	{
		return $this->hasOne(Project::class, 'idproyect', 'project_id');
	}

	public function codeWBS()
	{
		return $this->hasOne(CatCodeWBS::class, 'id', 'wbs_id');
	}

	public function user()
	{
		return $this->hasOne(User::class,'id', 'user_id');
	}

	public function causes()
	{
		return $this->hasMany(ActivityHasCause::class,'activity_id','id');
	}

	public function resources()
	{
		return $this->hasMany(ActivityHasResource::class,'activity_id','id');
	}

	public function hasCause($name)
	{
		return $this->hasOne(ActivityHasCause::class,'activity_id','id')->where('causes_code',$name);
	}

	public function hasResource($name)
	{
		return $this->hasOne(ActivityHasResource::class,'activity_id','id')->where('resource_code',$name);
	}
}
