<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillNominaIndemnification extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'total_paid',
		'service_year',
		'last_ordinary_monthly_salary',
		'cumulative_income',
		'non_cumulative_income',
		'bill_nomina_id',
	];
}