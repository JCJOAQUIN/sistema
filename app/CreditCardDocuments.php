<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditCardDocuments extends Model
{
	const CREATED_AT    = 'date';
	protected $fillable = 
	[
		'id',
		'idcreditCard',
		'path',
	];
}
