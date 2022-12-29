<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillNominaExtraHours extends Model
{
	public $timestamps = false;
	protected $fillable = 
	[
		'days',
		'hours',
		'amount',
		'cat_type_hour_id',
		'bill_nomina_id'
	];

	public function hourType()
	{
		return $this->hasOne(CatTypeHour::class,'id','cat_type_hour_id');
	}
}