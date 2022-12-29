<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketAnswer extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idAnswerTickets';
	protected $fillable   = 
	[
		'answer',
		'path',
		'date',
		'idTickets',
		'users_id',
	];

	public function documentsTickets()
	{
		return $this->hasMany(DocumentsTickets::class,'idAnswerTickets','idAnswerTickets');
	}

	public function answerTicket()
	{
		return $this->belongsTo(TicketAnswer::class,'idTickets','idTickets');
	}

	public function answerUser()
	{
		return $this->belongsTo(User::class,'users_id','id');
	}

	public function setPathAttribute($path)
	{
		if(!empty($path))
		{
			$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_ticketDocument.'.$path->getClientOriginalExtension();
			$name = '/docs/tickets/AdG'.round(microtime(true) * 1000).'_ticketDocument.'.$path->getClientOriginalExtension();
			\Storage::disk('public')->put($name,\File::get($path));
		}
	}
}
