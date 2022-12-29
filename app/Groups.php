<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Groups extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idgroups';
	protected $fillable   = 
	[
		'title',
		'datetitle',
		'numberOrder',
		'operationType',
		'amountMovement',
		'amountRetake',
		'commission',
		'reference',
		'typeCurrency',
		'paymentDate',
		'idpaymentMethod',
		'statusBill',
		'amount',
		'idFolio',
		'idKind',
		'idEnterpriseOrigin',
		'idDepartamentOrigin',
		'idAreaOrigin',
		'idProjectOrigin',
		'idAccAccOrigin',
		'idEnterpriseDestiny',
		'idAccAccDestiny',
		'idProvider',
		'provider_has_banks_id',
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

	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}

	public function detailGroups()
	{
		return $this->hasMany(GroupsDetail::class,'idgroups','idgroups');
	}

	public function documentsGroups()
	{
		return $this->hasMany(GroupsDocuments::class,'idgroups','idgroups');
	}

	public function provider()
	{
		return $this->belongsTo(Provider::class,'idProvider','idProvider');
	}

	public function bankData()
	{
		return $this->belongsTo(ProviderBanks::class,'provider_has_banks_id','id');
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
				$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_groupsDoc.'.$path->getClientOriginalExtension();
				$name = '/docs/movements/AdG'.round(microtime(true) * 1000).'_groupsDoc.'.$path->getClientOriginalExtension();
				\Storage::disk('public')->put($name,\File::get($path));
			}
		}
	}
}
