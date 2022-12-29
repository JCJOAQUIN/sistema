<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcurementPurchaseDocuments extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'id',
		'name',
		'path',
		'users_id',
		'created_at',
		'idprocurementPurchase',
	];
}
