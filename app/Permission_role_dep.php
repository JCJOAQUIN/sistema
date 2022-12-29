<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission_role_dep extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idpermission_role_dep';
	protected $fillable   = 
	[
		'idpermission_role_dep',
		'role_has_module_idrole_has_module',
		'departament_id',
	];
}
