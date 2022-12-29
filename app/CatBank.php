<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ActiveScope;

class CatBank extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'c_bank',
		'description',
		'businessName',
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
