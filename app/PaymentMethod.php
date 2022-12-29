<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idpaymentMethod';
	protected $fillable   = 
	[
		'idpaymentMethod',
		'method',
	];

	public function resource()
	{
		return $this->belongsTo(Resource::class,'idpaymentMethod','idpaymentMethod');
	}

	public function expense()
	{
		return $this->belongsTo(Expenses::class,'idpaymentMethod','idpaymentMethod');
	}

	public function scopeOrderName($query)
	{
		return $query->orderBy('method','asc');
	}
}
