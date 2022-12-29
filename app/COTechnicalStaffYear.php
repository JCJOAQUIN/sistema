<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COTechnicalStaffYear extends Model
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
