<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcurementHistory extends Model
{
	protected $fillable = 
	[
		'folio',
		'folio_original',
		'users_id',
	];

	public function requestModel()
	{
		return $this->hasOne(RequestModel::class,'folio','folio');
	}

	public function procurementPurchase()
	{
		return $this->hasOne(ProcurementPurchase::class,'id','folio');
	}
}
