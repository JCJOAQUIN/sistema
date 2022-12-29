<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionDep extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idpermission_department';
	protected $fillable   = 
	[
		'user_has_module_iduser_has_module',
		'departament_id',
	];
}
