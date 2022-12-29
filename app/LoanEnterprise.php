<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanEnterprise extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idloanEnterprise';
	protected $fillable   = 
	[
		'idloanEnterprise',
		'title',
		'datetitle',
		'tax',
		'amount',
		'currency',
		'paymentDate',
		'idpaymentMethod',
		'idEnterpriseOrigin',
		'idAccAccOrigin',
		'idEnterpriseDestiny',
		'idAccAccDestiny',
	];

	public function enterpriseOrigin()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterpriseOrigin');
	}

	public function enterpriseDestiny()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterpriseDestiny');
	}

	public function accountOrigin()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccAccOrigin');
	}

	public function accountDestiny()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccAccDestiny');
	}

	public function enterpriseOriginReviewed()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterpriseOriginR');
	}

	public function enterpriseDestinyReviewed()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterpriseDestinyR');
	}

	public function accountOriginReviewed()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccAccOriginR');
	}

	public function accountDestinyReviewed()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccAccDestinyR');
	}

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function documentsLoan()
	{
		return $this->hasMany(LoanEnterpriseDocuments::class,'idloanEnterprise','idloanEnterprise');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
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
				$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_LoanEntDoc.'.$path->getClientOriginalExtension();
				$name = '/docs/movements/AdG'.round(microtime(true) * 1000).'_LoanEntDoc.'.$path->getClientOriginalExtension();
				\Storage::disk('public')->put($name,\File::get($path));
			}
		}
	}
}
