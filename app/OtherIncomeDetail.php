<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OtherIncomeDetail extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'quantity',
		'description',
		'unit',
		'unit_price',
		'tax',
		'type_tax',
		'total_taxes',
		'total_retentions',
		'subtotal',
		'total',
		'idOtherIncome',
	];

	public function taxes()
	{
		return $this->hasMany(OtherIncomeDetailTaxes::class,'idOtherIncomeDetail','id')->where('type','I');
	}

	public function retentions()
	{
		return $this->hasMany(OtherIncomeDetailTaxes::class,'idOtherIncomeDetail','id')->where('type','R');
	}
}
