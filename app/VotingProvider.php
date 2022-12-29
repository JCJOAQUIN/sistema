<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VotingProvider extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'user_id',
		'idRequisitionHasProvider',
		'idRequisitionDetail',
		'idRequisition'
	];

	public function userData()
	{
		return $this->hasOne(User::class,'id','user_id');
	}

	public function requisitionHasProvider()
	{
		return $this->hasOne(RequisitionHasProvider::class,'id','idRequisitionHasProvider');
	}
}
