<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RefundDocuments extends Model
{
	public $timestamps     = false;
	protected $primaryKey  = 'idRefundDocuments';
	protected $fillable = 
	[
		'path',
		'idRefundDetail',
	];

	public function setPathAttribute($path)
	{
		if(is_string($path))
		{
			$this->attributes['path'] = $path;
		}
		else
		{
			$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_refundDoc.'.$path->getClientOriginalExtension();
			$name = '/docs/refounds/AdG'.round(microtime(true) * 1000).'_refundDoc.'.$path->getClientOriginalExtension();
			\Storage::disk('public')->put($name,\File::get($path));
		}
	}
}
