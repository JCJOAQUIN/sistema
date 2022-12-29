<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COCValuesThatApply extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'tipodeanticipo',
		'modelodecalculodelfinanciamiento',
		'interesesaconsiderarenelfinanciamiento',
		'tasaactiva',
		'calculodelcargoadicional',
		'diasaconsiderarenelaño',
	];
}
