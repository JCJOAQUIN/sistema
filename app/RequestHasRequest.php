<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequestHasRequest extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'folio',
		'children',
	];

	public function parentRequestModel()
	{
		return $this->belongsTo(RequestModel::class,'folio','folio');
	}

	public function childrenRequestModel()
	{
		return $this->belongsTo(RequestModel::class,'children','folio');
	}
}
