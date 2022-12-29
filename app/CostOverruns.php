<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CostOverruns extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idproyect',
		'file',
		'idCreate',
		'status',
		'name',
	];
	protected $status = 
	[
		0 => 'Subiendo',
		1 => 'Registrando',
		2 => 'Guardado',
		3 => 'Finalizado',
	];

	public function proyect()
	{
		return $this->hasOne(Project::class,'idproyect','idproyect');
	}

	public function getStatusAttribute($status)
	{
		return $this->status[$status];
	}

	public function generalesObra()
	{
		return $this->hasOne(CostOverrunsNCGConstruction::class, 'idUpload', 'id');
	}
}
