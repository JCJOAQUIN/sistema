<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FlightLodging extends Model
{
	protected $fillable = 
	[
		'folio_request',
		'pemex',
		'reference',
		'payment_method',
		'currency',
		'bill_status',
	];

	public function request()
	{
		return $this->hasOne(RequestModel::class, 'folio', 'folio_request');
	}

	public function details()
	{
		return $this->hasMany(FlightLodgingDetails::class);
	}

	public function documents()
	{
		return $this->hasMany(FlightLodgingDocuments::class,'flight_lodging_id');
	}

	public function paymentMethodData()
	{
		return $this->hasOne(PaymentMethod::class,'idpaymentMethod','payment_method');
	}

	public function requestedByPemex()
	{
		switch ($this->pemex_pti) 
		{
			case 0:
				return 'No';
				break;
			case 1:
				return 'Sí';
				break;
			default:
				return 'No definido';
				break;
		}
	}
}
