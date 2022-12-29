<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdjustmentFolios extends Model
{
	protected $primaryKey = 'idadjustmentFolios';
	public $timestamps    = false;
	protected $fillable   = 
	[
		'idadjustmentFolios',
		'idFolio',
	];

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}
}
