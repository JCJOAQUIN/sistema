<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idproyect';
	protected $fillable   = 
	[
		'proyectNumber',
		'proyectName',
		'projectCode',
		'description',
		'place',
		'kindOfProyect',
		'status',
		'obra',
		'placeObra',
		'city',
		'startObra',
		'endObra',
		'client',
		'contestNo',
		'requisition',
		'latitude',
		'longitude',
		'distance',
	];

	public function scopeOrderName($query)
	{
			return $query->orderBy('proyectName','asc');
	}

	public function codeWBS()
	{
		return $this->hasMany(CatCodeWBS::class,'project_id','idproyect');
	}

	public function contract()
	{
		return $this->hasOne(Contract::class,'project_id','idproyect');
	}
}
