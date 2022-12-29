<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ActiveScope;

class CatPeriodicity extends Model
{
	public $timestamps    = false;
	public $incrementing  = false;
	protected $primaryKey = 'c_periodicity';
	protected $fillable   = 
	[
		'description',
		'days',
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
