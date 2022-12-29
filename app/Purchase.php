<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idPurchase';
	protected $fillable   = 
	[
		'idPurchase',
		'idProvider',
		'idFolio',
		'idKind',
		'notes',
		'discount',
		'badge',
		'actspend',
		'paymentMode',
		'typeCurrency',
		'billStatus','path',
		'exitGroup',
		'subtotales',
		'amount',
	];

	public function partialPayment()
	{
		return $this->hasMany(PartialPayment::class,'purchase_id','idPurchase')->orderBy('date_requested','asc');
	}

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function detailPurchase()
	{
		return $this->hasMany(DetailPurchase::class,'idPurchase','idPurchase');
	}

	public function provider()
	{
		return $this->belongsTo(Provider::class,'idProvider','idProvider');
	}

	public function bankData()
	{
		return $this->belongsTo(ProviderBanks::class,'provider_has_banks_id','id');
	}

	public function documents()
	{
		return $this->hasMany(DocumentsPurchase::class,'idPurchase','idPurchase');
	}

	public function budget()
	{
		return $this->hasOne(Budget::class,'request_id','idFolio');
	}

	public function requisitionRequest()
	{
		return $this->hasOne(RequestModel::class,'folio','idRequisition');
	}

	public function getPresupuestoEstatusAttribute()
	{
		$p = $this->budget;
		if($p)
		{
			return $p->status == 1 ? "Aprobada" : "Rechazada";
		}
		else{
			return 'Pendiente';
		}
	}

	public function setPathAttribute($path)
	{
		if(is_string($path))
		{
			$this->attributes['path'] = $path;
		}
		else
		{
			if(!empty($path))
			{
				$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_expenseDoc.'.$path->getClientOriginalExtension();
				$name = '/docs/purchase/AdG'.round(microtime(true) * 1000).'_expenseDoc.'.$path->getClientOriginalExtension();
				\Storage::disk('public')->put($name,\File::get($path));
			}
		}
	}
}
