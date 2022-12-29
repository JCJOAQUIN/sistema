<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ControlDoc extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'name',
		'short_name',
	];

	public function controlInternal()
	{
		return $this->hasOne(ControlInternal::class, 'control_docs_id');
	}
}
