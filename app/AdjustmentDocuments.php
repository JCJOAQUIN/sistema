<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdjustmentDocuments extends Model
{
	const CREATED_AT      = 'date';
	protected $primaryKey = 'iddocumentsAdjustment';
	protected $fillable   = 
	[
		'path',
		'idadjustment',
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
				$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_LoanEntDoc.'.$path->getClientOriginalExtension();
				$name = '/docs/movements/AdG'.round(microtime(true) * 1000).'_LoanEntDoc.'.$path->getClientOriginalExtension();
				\Storage::disk('public')->put($name,\File::get($path));
			}
		}
	}
}
