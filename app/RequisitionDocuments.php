<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequisitionDocuments extends Model
{
	const CREATED_AT    = 'created';
	protected $fillable = 
	[
		'name',
		'path',
		'fiscal_folio',
		'datepath',
		'timepath',
		'ticket_number',
		'amount',
		'user_id',
		'idRequisition',
	];

	public function user()
	{
		return $this->hasOne(User::class,'id','user_id');
	}
}
