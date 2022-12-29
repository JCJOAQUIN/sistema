<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VersionLot extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idlot',
		'total',
		'subtotal',
		'iva',
		'articles',
		'date',
		'idEnterprise',
		'idElaborate',
		'account',
		'idFolio',
		'category',
	];

	public function enterprise()
	{
		return $this->belongsTo(Enterprise::class,'idEnterprise','id');
	}

}
