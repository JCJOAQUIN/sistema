<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COTechnicalStaffYearSalary extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'idConcept',
		'mes',
		'ano',
		'amount',
	];
}
