<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ControlBank extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'data',
		'TRASF_CH',
		'amount',
		'observations',
		'note',
	];

	public function controlInternal()
	{
		return $this->hasOne(ControlInternal::class, 'control_banks_id');
	}
}
