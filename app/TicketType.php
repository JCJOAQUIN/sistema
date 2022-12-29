<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idTypeTickets';
	protected $fillable   = 
	[
		'type',
	];

	public function scopeOrderName($query)
	{
		return $query->orderBy('type','asc');
	}
}
