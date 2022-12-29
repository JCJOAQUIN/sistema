<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionProject extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idpermission_project';
	protected $fillable   = 
	[
		'user_has_module_iduser_has_module',
		'project_id',
	];
}
