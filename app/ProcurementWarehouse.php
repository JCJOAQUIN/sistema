<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcurementWarehouse extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'description',
		'measure',
		'code_mat',
		'quantity_not_damaged',
		'damaged',
		'quantity',
		'unit_price',
		'total_art',
		'date_entry',
		'commentaries',
		'status',
		'idProcurementPurchaseDetail',
		'users_id',
	];
}
