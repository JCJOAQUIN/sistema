<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COCValues extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'costodirectodelaobra',
		'importetotaldelamanodeobragravable',
		'importetotaldelaobra',
		'factorparalaobtenciondelasfp',
		'porcentajedeutilidadbrutapropuesta',
		'tasadeinteresusada',
		'puntosdelbanco',
		'indicadoreconomicodereferencia',
		'isr',
		'ptu',
	];
}
