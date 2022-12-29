<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderClassificationDocs extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'path',
		'providerClassification_id',
	];

	public function providerClassification()
	{
		return $this->belongsTo(ProviderClassification::class,'providerClassification_id','id');
	}
}
