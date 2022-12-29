<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Adjustment extends Model
{
	protected $primaryKey = 'idadjustment';
	public $timestamps    = false;
	protected $fillable   = 
	[
		'title',
		'datetitle',
		'tax',
		'numberOrder',
		'folio',
		'commentaries',
		'currency',
		'paymentDate',
		'subtotales',
		'additionalTax',
		'retention',
		'amount',
		'notes',
		'idpaymentMethod',
		'idEnterpriseOrigin',
		'idAreaOrigin',
		'idDepartamentOrigin',
		'idAccAccOrigin',
		'idProjectOrigin',
		'idEnterpriseDestiny',
		'idAreaDestiny',
		'idDepartamentDestiny',
		'idAccAccDestiny',
		'idProjectDestiny',
		'idFolio',
		'idKind',
		'idEnterpriseOriginR',
		'idAreaOriginR',
		'idDepartamentOriginR',
		'idAccAccOriginR',
		'idProjectOriginR',
		'idEnterpriseDestinyR',
		'idAreaDestinyR',
		'idDepartamentDestinyR',
		'idAccAccDestinyR',
		'idProjectDestinyR',
	];
	
	public function requestModel()
	{
		return $this->belongsTo(RequestModel::class,'idFolio','folio');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class,'idpaymentMethod','idpaymentMethod');
	}

	public function detailAdjustment()
	{
		return $this->hasMany(AdjustmentDetail::class,'idadjustment','idadjustment');
	}

	public function adjustmentFolios()
	{
		return $this->hasMany(AdjustmentFolios::class,'idadjustment','idadjustment');
	}

	public function documentsAdjustment()
	{
		return $this->hasMany(AdjustmentDocuments::class,'idadjustment','idadjustment');
	}

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

	public function areaDestiny()
	{
		return $this->hasOne(Area::class,'id','idAreaDestiny');
	}

	public function departmentOrigin()
	{
		return $this->hasOne(Department::class,'id','idDepartamentOrigin');
	}

	public function departmentDestiny()
	{
		return $this->hasOne(Department::class,'id','idDepartamentDestiny');
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
				$this->attributes['path'] = 'AdG'.round(microtime(true) * 1000).'_adjustmentDoc.'.$path->getClientOriginalExtension();
				$name = '/docs/movements/AdG'.round(microtime(true) * 1000).'_adjustmentDoc.'.$path->getClientOriginalExtension();
				\Storage::disk('public')->put($name,\File::get($path));
			}
		}
	}
}
