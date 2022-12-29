<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanEnterpriseDocuments extends Model
{
	const CREATED_AT      = 'date';
	protected $primaryKey = 'iddocumentsLoanEnterprise';
	protected $fillable   = 
	[
		'idloanEnterprise',
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
				$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_loanEntDoc.'.$path->getClientOriginalExtension();
				$name = '/docs/movements/AdG'.round(microtime(true) * 1000).'_loanEntDoc.'.$path->getClientOriginalExtension();
				\Storage::disk('public')->put($name,\File::get($path));
			}
		}
	}
}
