<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lot extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idlot';
	protected $fillable   = 
	[
		'total',
		'subtotal',
		'iva',
		'articles',
		'date',
		'idEnterprise',
		'idKind',
		'idElaborate',
		'fileName',
		'filePath',
		'status',
		'account',
		'category',
	];

	public function enterprise()
	{
		return $this->belongsTo(Enterprise::class,'idEnterprise','id');
	}

	public function warehouseStationary()
	{
		return $this->hasMany(Warehouse::class,'idLot','idlot');
	}

	public function kind()
	{
		return $this->belongsTo(RequestKind::class,'idKind','idrequestkind');
	}

	public function documents()
	{
		return $this->hasMany(DocumentsWarehouse::class,'idlot','idlot');
	}

	public function versions()
	{
		return $this->hasMany(VersionLot::class,'idlot','idlot');
	}

	public function accounts()
	{
		return $this->belongsTo(Account::class,'account','idAccAcc');
	}

	public function purchase()
	{
		return $this->hasOne(Purchase::class,'idFolio','idFolio');
	}

	public function idFolioPurchase()
	{
		return $this->hasOne(LotIdFolios::class,'idLot','idlot');
	}
}
