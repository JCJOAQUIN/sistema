<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderClassification extends Model
{
	protected $fillable = 
	[
		'provider_id',
		'classification',
		'commentary',
		'created_by',
	];

	public function provider()
	{
		return $this->belongsTo(Provider::class,'provider_id','idProvider');
	}

	public function docs()
	{
		return $this->hasMany(ProviderClassificationDocs::class,'providerClassification_id','id');
	}
}
