<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleDocument extends Model
{
	protected $fillable = 
	[
		'name',
		'path',
		'cat_type_document',
		'vehicles_mechanical_services_id',
		'vehicles_fines_id',
		'vehicles_taxes_id',
		'vehicles_fuel_id',
		'vehicles_id',
		'users_id',
	];
}
