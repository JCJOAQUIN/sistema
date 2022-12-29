<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResourceTemp extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'total',
		'reference',
		'idEmployee',
		'idpaymentMethod',
		'currency',
		'idAutomaticRequests',
	];

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}

	public function resourceDetail()
	{
		return $this->hasMany(ResourceDetailTemp::class,'idResourceTemp','id');
	}

	public function bankData()
	{
		return $this->belongsTo(Employee::class,'idEmployee','idEmployee');
	}
}
