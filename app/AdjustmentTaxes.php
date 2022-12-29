<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdjustmentTaxes extends Model
{
	protected $primaryKey = 'idtaxesAdjustment';
	public $timestamps    = false;
	protected $fillable   = 
	[
		'name',
		'amount',
		'idadjustmentDetail'
	];
}
