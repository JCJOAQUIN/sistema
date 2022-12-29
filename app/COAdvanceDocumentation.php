<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COAdvanceDocumentation extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'idDocEmpresa',
		'unanticipo',
		'dosanticipo',
		'rebasa',
	];
}
