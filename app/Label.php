<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idlabels';
	protected $fillable   = 
	[
		'idlabels',
		'description',
	];

	public function requestModel()
	{
		return $this->belongsToMany(RequestModel::class,'request_has_labels','labels_idlabels','request_folio')
			->withPivot('request_kind');
	}

	public function scopeOrderName($query)
	{
		return $query->orderBy('description','asc');
	}
}
