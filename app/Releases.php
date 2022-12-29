<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Releases extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idreleases';
	protected $fillable   = 
	[
		'title',
		'content',
		'visible',
		'date',
	];

	public function setPathAttribute($path)
	{
		if(is_string($path))
		{
			$this->attributes['path'] = $path;
		}
		else
		{
			if(!empty($path))
			{
				$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_release.'.$path->getClientOriginalExtension();
				$name = '/images/releases/AdG'.round(microtime(true) * 1000).'_release.'.$path->getClientOriginalExtension();
				\Storage::disk('public')->put($name,\File::get($path));
			}
		}
	}
}
