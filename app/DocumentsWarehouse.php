<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentsWarehouse extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'iddocumentsWarehouse';
	protected $fillable   = 
	[
		'path',
		'idlot',
	];

	public function setPathAttribute($path)
	{
		if(is_string($path))
		{
			$this->attributes['path'] = $path;
		}
		else
		{
			$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_warehouse.'.$path->getClientOriginalExtension();
			$name = '/docs/warehouse/AdG'.round(microtime(true) * 1000).'_warehouse.'.$path->getClientOriginalExtension();
			\Storage::disk('public')->put($name,\File::get($path));
		}
	}
}
