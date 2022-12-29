<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkOrderDocuments extends Model
{
	const CREATED_AT    = 'created';
	protected $fillable = 
	[
		'name',
		'path',
		'idWorkOrder',
		'user_id',
	];

	public function user()
	{
		return $this->hasOne(User::class,'id','user_id');
	}
}
