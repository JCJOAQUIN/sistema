<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehouseRemove extends Model
{
	protected $fillable = 
	[
		'warehouse_id',
		'lot_id',
		'user_id',
		'quantity',
		'reasons'
	];

	public function warehouse()
	{
		return $this->hasOne('App\Warehouse','warehouse_id','idwarehouse');
	}

	public function lot()
	{
		return $this->hasOne('App\Lot','lot_id','idLot');
	}

	public function user()
	{
		return $this->hasOne('App\User');
	}
}
