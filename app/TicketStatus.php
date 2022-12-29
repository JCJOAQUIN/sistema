<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idStatusTickets';
	protected $fillable   = 
	[
		'status',
	];

	public function scopeOrderName($query)
	{
		return $query->orderBy('status','asc');
	}
}
