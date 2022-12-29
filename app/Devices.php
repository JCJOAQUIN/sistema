<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Devices extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'iddevices';
	protected $fillable   = 
	[ 
		'brand',
		'type',
		'characteristics',
		'cost',
		'buyDate',
		'assign',
	];

	public function detailComputer()
	{
		return $this->hasMany(DetailComputer::class,'idDevices','iddevices');
	}
}