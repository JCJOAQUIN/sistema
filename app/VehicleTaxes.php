<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleTaxes extends Model
{
	protected $fillable =
	[
		'date_verification',
		'next_date_verification',
		'total',
		'monto_gestoria',
		'vehicles_id',
		'users_id',
	];

	public function documents()
	{
		return $this->hasMany(VehicleDocument::class,'vehicles_taxes_id','id');
	}
}
