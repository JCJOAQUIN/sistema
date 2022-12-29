<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequisitionHasProvider extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idProviderSecondary',
		'idRequisition',
		'users_id',
	];

	public function providerData()
	{
		return $this->hasOne(ProviderSecondary::class,'id','idProviderSecondary');
	}

	public function votingProvider()
	{
		return $this->hasMany(VotingProvider::class,'idRequisitionHasProvider','id');
	}

	public function documents()
	{
		return $this->hasMany(RequisitionHasProviderDocuments::class,'idRequisitionHasProvider','id');
	}
}
