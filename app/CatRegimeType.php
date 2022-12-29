<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ActiveScope;

class CatRegimeType extends Model
{
	public $timestamps   = false;
	public $incrementing = false;
	protected $keyType   = 'string';
	protected $fillable  = 
	[
		'description',
		'validity_start',
		'validity_end',
	];

	protected static function boot()
	{
		parent::boot();
		static::addGlobalScope(new ActiveScope);
	}
	
	public function scopeOrderName($query)
	{
		return $query->orderBy('description','asc');
	}

}
