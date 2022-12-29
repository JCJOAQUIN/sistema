<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ActiveScope;
use App\Scopes\CfdiVesionScope;

class CatRelation extends Model
{
	public $timestamps    = false;
	public $incrementing  = false;
	protected $primaryKey = 'typeRelation';
	protected $keyType    = 'string';
	protected $fillable   = 
	[
		'description',
		'validity_start',
		'validity_end',
		'cfdi_3_3',
		'cfdi_4_0',
	];

	protected static function boot()
	{
		parent::boot();
		static::addGlobalScope(new ActiveScope);
		static::addGlobalScope(new CfdiVesionScope);
	}
}
