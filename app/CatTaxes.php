<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ActiveScope;

class CatTaxes extends Model
{
	public $timestamps    = false;
	public $incrementing  = false;
	protected $primaryKey = 'tax';
	protected $keyType    = 'string';
	protected $fillable   = 
	[
		'description',
		'retention',
		'transfer',
		'validity_start',
		'validity_end',
	];

	protected static function boot()
	{
		parent::boot();
		static::addGlobalScope(new ActiveScope);
	}
}
