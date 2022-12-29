<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseRecordDocuments extends Model
{
	const CREATED_AT    = 'date';
	protected $fillable = 
	[
		'idPurchaseRecord',
		'path',
	];
}
