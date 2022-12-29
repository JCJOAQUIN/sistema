<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatusRequest extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idrequestStatus';
	protected $fillable   = 
	[
		'description',
	];

	public function requeststatus()
	{
		return $this->belongsTo(RequestModel::class,'idrequestStatus','status');
	}

	public function scopeOrderName($query)
	{
		return $query->orderBy('description','asc');
	}
}
