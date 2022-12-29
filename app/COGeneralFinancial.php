<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COGeneralFinancial extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'indicadoreconomicodereferencia',
		'puntosdeintermediaciondelabanca',
		'tasadeinteresdiaria',
		'diasparapagodeestimaciones',
		'aplicablealperiodo',
		'porcentajedefinancieamiento',
	];
}
