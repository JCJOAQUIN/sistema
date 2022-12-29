<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ActiveScope;

class CatTaxRegime extends Model
{
	public $timestamps    = false;
	public $incrementing  = false;
	protected $primaryKey = 'taxRegime';
	protected $keyType    = 'string';
	protected $fillable   = 
	[
		'description',
		'physical',
		'moral',
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
