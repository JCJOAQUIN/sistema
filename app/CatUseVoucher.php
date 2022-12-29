<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ActiveScope;
use App\Scopes\CfdiVesionScope;

class CatUseVoucher extends Model
{
	public $timestamps    = false;
	public $incrementing  = false;
	protected $primaryKey = 'useVoucher';
	protected $keyType    = 'string';
	protected $fillable   = 
	[
		'useVoucher',
		'description',
		'physical',
		'moral',
		'validity_start',
		'validity_end',
		'tax_regime_receptor',
		'cfdi_3_3',
		'cfdi_4_0',
	];

	protected static function boot()
	{
		parent::boot();
		static::addGlobalScope(new ActiveScope);
		static::addGlobalScope(new CfdiVesionScope);
	}

	public function scopeOrderName($query)
	{
		return $query->orderBy('description','asc');
	}
}
