<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequisitionHasProviderDocuments extends Model
{
	const CREATED_AT    = 'created';
	protected $fillable = 
	[
		'name',
		'path',
		'idRequisitionHasProvider',
		'user_id',
	];

	public function user()
	{
		return $this->hasOne(User::class,'id','user_id');
	}
}
