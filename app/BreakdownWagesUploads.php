<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BreakdownWagesUploads extends Model
{
	protected $fillable = 
	[
		'idproyect',
		'file',
		'idCreate',
		'status',
		'name',
		'client',
		'contestNo',
		'obra',
		'place',
		'city',
		'startObra',
		'endObra',
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
		return $this->hasOne(Project::class, 'idproyect', 'idproyect');
	}

	public function getStatusAttribute($status)
	{
		return $this->status[$status];
	}
}
