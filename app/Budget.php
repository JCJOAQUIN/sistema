<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'request_id',
		'user_id',
		'status',
		'comment',
	];

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'folio','request_id');
	}

	public function budgetUser()
	{
		return $this->hasOne(User::class,'id','user_id');
	}
}
