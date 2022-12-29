<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailComputer extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idDetComputer';
	protected $fillable   = 
	[
		'idDetComputer',
		'idComputer',
		'idDevices',
		'assignedDate',
		'configuration',
	];

	public function computer()
	{
		return $this->belongsTo(Computer::class, 'idComputer','idComputer');
	}

	public function devices()
	{
		return $this->belongsTo(Devices::class, 'idDevices','iddevices');
	}
}
