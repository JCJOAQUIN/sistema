<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatContractItem extends Model
{
	protected $fillable = 
	[
		'contract_item',
		'activity',
		'unit',
		'pu',
		'amount',
		'contract_id'
	];

	public function contractData()
	{
		return $this->hasMany(Contract::class,'id','contract_id');
	}
}
