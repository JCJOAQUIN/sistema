<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComputerEquipment extends Model
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

	public function typeEquipment()
	{
		switch ($this->type) 
		{
			case "1":
				return "Smartphone";
				break;

			case "2":
				return "Tablet";
				break;

			case "3":
				return "Laptop";
				break;

			case "4":
				return "Desktop";
				break;
			
			default:
				break;
		}
	}
}
