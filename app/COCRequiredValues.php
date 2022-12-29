<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COCRequiredValues extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'anticipoaproveedoresaliniciodeobra',
		'porcentajedeimpuestosobrenomina',
		'presentaciondespuesdelcorte',
		'revisionyautorizacion',
		'diasparaelpago',
		'totaldedias',
		'periododecobroprimeraestimacion',
		'periododeentregasegundoanticipo',
		'redondeoparaprogramadepersonaltecnico',
		'presentaciondelprogramadepersonaltecnico',
		'horasjornada',
	];

	public function getTotaldediasAttribute()
	{
		return ($this->presentaciondespuesdelcorte+$this->revisionyautorizacion+$this->diasparaelpago);
	}
}
