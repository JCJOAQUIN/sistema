<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Module extends Model
{
	protected $fillable = 
	[
		'name',
		'father',
		'category',
		'details',
		'icon',
		'url',
		'permissionRequire',
		'itemOrder',
		'hybrid',
		'active',
		'global_permission'
	];

	protected static function boot()
	{
		parent::boot();
		static::addGlobalScope('active', function (Builder $builder)
		{
			$builder->where('active', 1);
		});
	}

	public function role()
	{
		return $this->belongsToMany(Role::class,'role_has_modules','module_id','role_id');
	}

	public function user()
	{
		return $this->belongsToMany(User::class,'user_has_modules','module_id','user_id');
	}

	public function fatherModule()
	{
		return $this->belongsTo(Module::class,'father','id');
	}

	public function childrenModule()
	{
		return $this->hasMany(Module::class,'father','id');
	}

	public function tutorials()
	{
		return $this->hasMany(VideoTutorial::class);
	}
}
