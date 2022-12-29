<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ObraProgramDetails extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idObraProgramConcept',
		'amount',
		'type',
		'order',
	];
}