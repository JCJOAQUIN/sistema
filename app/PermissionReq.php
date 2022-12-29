<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionReq extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'user_has_module_id',
		'requisition_type_id',
	];
}
