<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NominaApplication extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idNominaApplication';
	protected $fillable   = 
	[
		'idProyect',
		'idFolio',
		'idKind',
	];

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function noAppEmp()
	{
		return $this->hasMany(NominaAppEmp::class,'idNominaApplication','idNominaApplication');
	}
}
