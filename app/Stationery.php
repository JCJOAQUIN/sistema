<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stationery extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idStationery';
	protected $fillable   = 
	[
		'idFolio',
		'idKind',
		'delivery',
		'title',
		'datetitle',
		'subtotal',
		'iva',
		'total',
		'subcontractorProvider',
	];

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function detailStat()
	{
		return $this->hasMany(DetailStationery::class,'idStat','idStationery');
	}
}
