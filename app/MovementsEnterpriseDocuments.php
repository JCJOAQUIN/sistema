<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MovementsEnterpriseDocuments extends Model
{
	const CREATED_AT      = 'date';
	protected $primaryKey = 'idmovementsEnterpriseDocuments';
	protected $fillable   = 
	[
		'idmovementsEnterprise',
		'path',
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
				$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_movementsEntDoc.'.$path->getClientOriginalExtension();
				$name = '/docs/movements/AdG'.round(microtime(true) * 1000).'_movementsEntDoc.'.$path->getClientOriginalExtension();
				\Storage::disk('public')->put($name,\File::get($path));
			}
		}
	}
}
