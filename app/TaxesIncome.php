<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxesIncome extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idtaxesIncome';
	protected $fillable   = 
	[
		'name',
		'amount',
		'idincomeDetail',
	];
}
