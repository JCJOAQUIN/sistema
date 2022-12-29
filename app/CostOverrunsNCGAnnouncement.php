<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CostOverrunsNCGAnnouncement extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'numconvocatoria',
		'fechaconvocatoria',
		'tipodelicitacion',
	];
}
