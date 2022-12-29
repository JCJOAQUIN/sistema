<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseEnterprise extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idpurchaseEnterprise';
	protected $fillable   = 
	[
		'title',
		'datetitle',
		'tax',
		'numberOrder',
		'currency',
		'paymentDate',
		'amount',
		'idpaymentMethod',
		'idEnterpriseOrigin',
		'idAreaOrigin',
		'idDepartamentOrigin',
		'idAccAccOrigin',
		'idProjectOrigin',
		'idEnterpriseDestiny',
		'idAccAccDestiny',
		'idProjectDestiny',
		'idFolio',
		'idKind',
		'idbanksAccounts',
	];

	public function enterpriseOrigin()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterpriseOrigin');
	}

	public function enterpriseDestiny()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterpriseDestiny');
	}

	public function areaOrigin()
	{
		return $this->hasOne(Area::class,'id','idAreaOrigin');
	}

	public function departmentOrigin()
	{
		return $this->hasOne(Department::class,'id','idDepartamentOrigin');
	}

	public function accountOrigin()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccAccOrigin');
	}

	public function accountDestiny()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccAccDestiny');
	}

	public function projectOrigin()
	{
		return $this->hasOne(Project::class,'idproyect','idProjectOrigin');
	}

	public function projectDestiny()
	{
		return $this->hasOne(Project::class,'idproyect','idProjectDestiny');
	}

	public function enterpriseOriginReviewed()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterpriseOriginR');
	}

	public function enterpriseDestinyReviewed()
	{
		return $this->hasOne(Enterprise::class,'id','idEnterpriseDestinyR');
	}

	public function areaOriginReviewed()
	{
		return $this->hasOne(Area::class,'id','idAreaOriginR');
	}

	public function departmentOriginReviewed()
	{
		return $this->hasOne(Department::class,'id','idDepartamentOriginR');
	}

	public function accountOriginReviewed()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccAccOriginR');
	}

	public function accountDestinyReviewed()
	{
		return $this->hasOne(Account::class,'idAccAcc','idAccAccDestinyR');
	}

	public function projectOriginReviewed()
	{
		return $this->hasOne(Project::class,'idproyect','idProjectOriginR');
	}

	public function projectDestinyReviewed()
	{
		return $this->hasOne(Project::class,'idproyect','idProjectDestinyR');
	}

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}

	public function detailPurchaseEnterprise()
	{
		return $this->hasMany(PurchaseEnterpriseDetail::class,'idpurchaseEnterprise','idpurchaseEnterprise');
	}

	public function documentsPurchase()
	{
		return $this->hasMany(PurchaseEnterpriseDocuments::class,'idpurchaseEnterprise','idpurchaseEnterprise');
	}

	public function banks()
	{
		return $this->hasOne(BanksAccounts::class,'idbanksAccounts','idbanksAccounts');
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
				$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_purchaseDoc.'.$path->getClientOriginalExtension();
				$name = '/docs/movements/AdG'.round(microtime(true) * 1000).'_purchaseDoc.'.$path->getClientOriginalExtension();
				\Storage::disk('public')->put($name,\File::get($path));
			}
		}
	}
}
