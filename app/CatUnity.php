<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ActiveScope;

class CatUnity extends Model
{
	public $timestamps    = false;
	public $incrementing  = false;
	protected $primaryKey = 'keyUnit';
	protected $keyType    = 'string';
	protected $fillable   = 
	[
		'name',
		'validity_start',
		'validity_end',
	];

	protected static function boot()
	{
		parent::boot();
		static::addGlobalScope(new ActiveScope);
	}
}
