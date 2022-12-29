<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role_has_module extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idrole_has_module';
	protected $fillable   = 
	[
		'role_id',
		'module_id',
	];
}
