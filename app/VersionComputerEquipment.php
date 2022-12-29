<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VersionComputerEquipment extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'quantity',
		'type',
		'brand',
		'storage',
		'processor',
		'ram',
		'sku',
		'amountUnit',
		'commentaries',
		'typeTax',
		'subtotal',
		'iva',
		'amountTotal',
		'idEnterprise',
		'account',
		'place_location',
		'idElaborate',
		'idComputer',
		'date',
	];

	public function enterprise()
	{
		return $this->belongsTo(Enterprise::class,'idEnterprise','id');
	}

	public function accounts()
	{
		return $this->belongsTo(Account::class,'account','idAccAcc');
	}

	public function location()
	{
		return $this->hasOne(Place::class,'id','place_location');
	}
}
