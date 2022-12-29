<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idIncome';
	protected $fillable   = 
	[
		'idClient',
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
		'tax',
		'idbanksAccounts',
	];

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function incomeDetail()
	{
		return $this->hasMany(IncomeDetail::class,'idIncome','idIncome');
	}

	public function client()
	{
		return $this->belongsTo(Clients::class,'idClient','idClient');
	}

	public function bankData()
	{
		return $this->belongsTo(BanksAccounts::class,'idbanksAccounts','idbanksAccounts');
	}

	public function documents()
	{
		return $this->hasMany(DocumentsIncome::class,'idIncome','idIncome');
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
				$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_incomeDoc.'.$path->getClientOriginalExtension();
				$name = '/docs/income/AdG'.round(microtime(true) * 1000).'_incomeDoc.'.$path->getClientOriginalExtension();
				\Storage::disk('public')->put($name,\File::get($path));
			}
		}
	}
}
