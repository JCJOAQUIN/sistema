<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentsPayments extends Model
{
	const CREATED_AT      = 'created';
	protected $primaryKey = 'iddocumentsPayments';
	protected $fillable   = 
	[
		'path',
		'idpayment',
	];
}
