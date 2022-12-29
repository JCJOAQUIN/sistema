<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionEnt extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idpermission_enterprise';
	protected $fillable   = 
	[
		'user_has_module_iduser_has_module',
		'enterprise_id',
	];
}
