<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
	protected $fillable = 
	[
		'name',
		'details',
		'status',
	];

	public function user()
	{
		return $this->hasOne(User::class,'role_id','id');
	}

	public function module()
	{
		return $this->belongsToMany(Module::class, 'role_has_module','role_id','module_id');
	}
}
