<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COCostPeriodProgram extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'idProgramado',
		'totalcostodirecto',
		'costomateriales',
		'costomanodeobra',
		'costoequipo',
		'costootrosinsumos',
	];

	public function getTotalcostodirectoAttribute()
	{
		return ($this->costomateriales+$this->costomanodeobra+$this->costoequipo);
	}
}
