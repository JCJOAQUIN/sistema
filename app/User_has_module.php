<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_has_module extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'iduser_has_module';
	protected $fillable   = 
	[
		'user_id',
		'module_id',
	];

	public function department()
	{
		return $this->hasMany(PermissionDep::class,'user_has_module_iduser_has_module','iduser_has_module');
	}

	public function enterprise()
	{
		return $this->hasMany(PermissionEnt::class,'user_has_module_iduser_has_module','iduser_has_module');
	}

	public function requisition()
	{
		return $this->hasMany(PermissionReq::class,'user_has_module_id','iduser_has_module');
	}

	public function project()
	{
		return $this->hasMany(PermissionProject::class,'user_has_module_iduser_has_module','iduser_has_module');
	}

	public function uploadFile()
	{
		return $this->hasMany(PermissionUploadFile::class,'user_has_module_id','iduser_has_module');
	}
}
