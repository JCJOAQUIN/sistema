<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxesExpenses extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idtaxesExpenses';
	protected $fillable   = 
	[
		'name',
		'amount',
		'idExpensesDetail',
	];
}
