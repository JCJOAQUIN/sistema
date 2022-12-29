<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketPriority extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idPriorityTickets';
	protected $fillable   = 
	[
		'priority',
	];

	public function scopeOrderName($query)
	{
		return $query->orderBy('priority','asc');
	}
}
