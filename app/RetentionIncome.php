<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RetentionIncome extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idretentionIncome';
	protected $fillable   = 
	[
		'name',
		'amount',
		'idincomeDetail',
	];
}
