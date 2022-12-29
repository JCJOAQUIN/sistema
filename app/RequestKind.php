<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequestKind extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idrequestkind';
	protected $fillable   = 
	[
		'idrequestkind',
		'kind',
	];

	public function request()
	{
		return $this->hasMany(RequestModel::class,'kind','idrequestkind');
	}

	public function scopeOrderName($query)
	{
		return $query->orderBy('kind','asc');
	}
}