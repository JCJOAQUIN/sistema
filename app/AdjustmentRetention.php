<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdjustmentRetention extends Model
{
	protected $primaryKey = 'idretentionAdjustment';
	public $timestamps    = false;
	protected $fillable   = 
	[
		'name',
		'amount',
		'idadjustmentDetail'
	];
}
