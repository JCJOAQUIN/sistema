<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ActiveScope;

class CatZipCode extends Model
{
	public $timestamps    = false;
	public $incrementing  = false;
	protected $primaryKey = null;
	protected $fillable   = 
	[
		'zip_code',
		'state',
		'validity_start',
		'validity_end',
	];

	protected static function boot()
	{
		parent::boot();
		static::addGlobalScope(new ActiveScope);
	}
}
