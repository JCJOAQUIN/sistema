<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LotIdFolios extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idLot',
		'idComputer',
		'idFolio',
	];

	public function lot()
	{
		return $this->hasOne(Lot::class,'idlot','idLot');
	}
}
