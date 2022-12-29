<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idTickets';
	protected $fillable   = 
	[
		'subject',
		'question',
		'path',
		'request_date',
		'request_id',
		'assigned_id',
		'idTypeTickets',
		'idPriorityTickets',
		'idStatusTickets',
		'idSectionTickets',
	];

	public function requestUser()
	{
		return $this->hasOne(User::class,'id','request_id');
	}

	public function assignedUser()
	{
		return $this->hasOne(User::class,'id','assigned_id');
	}

	public function documentsTickets()
	{
		return $this->hasMany(DocumentsTickets::class,'idTickets','idTickets');
	}

	public function statusTicket()
	{
		return $this->belongsTo(TicketStatus::class,'idStatusTickets','idStatusTickets');
	}

	public function priorityTicket()
	{
		return $this->belongsTo(TicketPriority::class,'idPriorityTickets','idPriorityTickets');
	}

	public function typeTicket()
	{
		return $this->belongsTo(TicketType::class,'idTypeTickets','idTypeTickets');
	}

	public function answerTicket()
	{
		return $this->hasMany(TicketAnswer::class,'idTickets','idTickets');
	}

	public function sectionTicket()
	{
		return $this->belongsTo(SectionTickets::class,'idSectionTickets','idsectionTickets');
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

	public function scopeSearch($query,$idSection,$idticket,$subject,$type,$priority,$status,$section,$min,$max,$user)
	{
		$query->whereNotNull('idTickets')
			->whereIn('idSectionTickets',$idSection)
			->where(function ($query) use ($idticket,$subject,$type,$priority,$status,$section,$min,$max,$user)
			{
				if ($user != "") 
				{
					if ($user == "Sin asignar") 
					{
						$query->where('assigned_id',null);
					}
					else
					{
						$query->where('assigned_id',$user);
					}
				}
				if ($idticket != "") 
				{
					$query->where('idtickets',$idticket);
				}
				if ($subject != "") 
				{
					$query->where('subject',$subject);
				}
				if ($type != "") 
				{
					$query->where('idTypeTickets',$type);
				}
				if ($priority != "") 
				{
					$query->where('idPriorityTickets',$priority);
				}
				if ($status != "") 
				{
					$query->where('idStatusTickets',$status);
				}
				if ($section != "") 
				{
					$query->where('idSectionTickets',$section);
				}
				if($min != "" && $max != "")
				{
					$query->whereBetween('request_date',[''.$min.' '.date('00:00:00').'',''.$max.' '.date('23:59:59').'']);
				}
			});
	}
}