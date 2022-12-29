<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentsTickets extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'iddocumentsTickes';
	protected $fillable   = 
	[
		'path',
		'idTickets',
		'idAnswerTickets',
	];
	
	public function setPathAttribute($path)
	{
		if(is_string($path))
		{
			$this->attributes['path'] = $path;
		}
		else
		{
			$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_ticketDocument.'.$path->getClientOriginalExtension();
			$name = '/docs/tickets/AdG'.round(microtime(true) * 1000).'_ticketDocument.'.$path->getClientOriginalExtension();
			\Storage::disk('public')->put($name,\File::get($path));
		}
	}
}
