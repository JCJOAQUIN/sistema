<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
	protected $fillable = 
	[
		'project_id',
		'wbs_id',
		'contractor_id',
		'contract',
		'type_audit',
		'date',
		'auditor',
		'user_id',
		'people_involved',
		'cat_auditor_id',
		'pti_responsible',
		'observations',
	];

	public function unsafeAct()
	{
		return $this->hasMany(UnsafeAct::class);
	}

	public function unsafeConditions()
	{
		return $this->hasMany(UnsafeCondition::class);
	}

	public function unsafePractices()
	{
		return $this->hasMany(UnsafePractice::class);
	}

	public function projectData()
	{
		return $this->hasOne(Project::class,'idproyect','project_id');
	}

	public function wbsData()
	{
		return $this->hasOne(CatCodeWBS::class,'id','wbs_id');
	}

	public function contractorData()
	{
		return $this->hasOne(Contractor::class,'id','contractor_id');
	}

	public function typeAudit()
	{
		switch ($this->type_audit) 
		{
			case '1':
				return 'Gerencial';
				break;

			case '2':
				return 'Línea de Mando';
				break;

			case '3':
				return 'Referencia';
				break;
			
			default:
				// code...
				break;
		}
	}

	public function statusData()
	{
		switch ($this->status) 
		{
			case '1':
				return 'Abierto';
				break;

			case '2':
				return 'Cerrado';
				break;
			
			default:
				// code...
				break;
		}
	}

	public function auditorData()
	{
		return $this->hasOne(CatAuditor::class,'id','cat_auditor_id');
	}

	public function othersAuditors()
	{
		return $this->hasMany(AuditHasOtherAuditor::class);
	}

	public function othersAuditorsExists($name)
	{
		return $this->hasOne(AuditHasOtherAuditor::class)->where('name',$name)->where('type',1);
	}

	public function othersAuditorsNew()
	{
		return $this->hasMany(AuditHasOtherAuditor::class)->where('type',2);
	}

	public function othersResponsibles()
	{
		return $this->hasMany(AuditHasOtherResponsible::class);
	}

	public function countDangerousnessOneThird()
	{
		$conditions	= $this->hasMany(UnsafeCondition::class)->where('dangerousness','1/3')->count();
		$acts		= $this->hasMany(UnsafeAct::class)->where('dangerousness','1/3')->count();
		$practices	= $this->hasMany(UnsafePractice::class)->where('dangerousness','1/3')->count();

		$sum = $conditions + $acts + $practices;
		return $sum;
	}

	public function countDangerousnessOne()
	{
		$conditions	= $this->hasMany(UnsafeCondition::class)->where('dangerousness','1')->count();
		$acts		= $this->hasMany(UnsafeAct::class)->where('dangerousness','1')->count();
		$practices	= $this->hasMany(UnsafePractice::class)->where('dangerousness','1')->count();

		$sum = $conditions + $acts + $practices;
		return $sum;
	}

	public function countDangerousnessThree()
	{
		$conditions	= $this->hasMany(UnsafeCondition::class)->where('dangerousness','3')->count();
		$acts		= $this->hasMany(UnsafeAct::class)->where('dangerousness','3')->count();
		$practices	= $this->hasMany(UnsafePractice::class)->where('dangerousness','3')->count();

		$sum = $conditions + $acts + $practices;
		return $sum;
	}

	public function countDangerousnessOneThirdSubcategory($id)
	{
		$conditions	= $this->hasMany(UnsafeCondition::class)->where('dangerousness','1/3')->where('subcategory_id',$id)->count();
		$acts		= $this->hasMany(UnsafeAct::class)->where('dangerousness','1/3')->where('subcategory_id',$id)->count();
		$practices	= $this->hasMany(UnsafePractice::class)->where('dangerousness','1/3')->where('subcategory_id',$id)->count();

		$sum = $conditions + $acts + $practices;
		return $sum;
	}

	public function countDangerousnessOneSubcategory($id)
	{
		$conditions	= $this->hasMany(UnsafeCondition::class)->where('dangerousness','1')->where('subcategory_id',$id)->count();
		$acts		= $this->hasMany(UnsafeAct::class)->where('dangerousness','1')->where('subcategory_id',$id)->count();
		$practices	= $this->hasMany(UnsafePractice::class)->where('dangerousness','1')->where('subcategory_id',$id)->count();

		$sum = $conditions + $acts + $practices;
		return $sum;
	}

	public function countDangerousnessThreeSubcategory($id)
	{
		$conditions	= $this->hasMany(UnsafeCondition::class)->where('dangerousness','3')->where('subcategory_id',$id)->count();
		$acts		= $this->hasMany(UnsafeAct::class)->where('dangerousness','3')->where('subcategory_id',$id)->count();
		$practices	= $this->hasMany(UnsafePractice::class)->where('dangerousness','3')->where('subcategory_id',$id)->count();

		$sum = $conditions + $acts + $practices;
		return $sum;
	}

	public function statusAverage()
	{
		$conditions	= $this->hasMany(UnsafeCondition::class)->count();
		$acts		= $this->hasMany(UnsafeAct::class)->count();
		$practices	= $this->hasMany(UnsafePractice::class)->count();

		if ($acts > 0 || $conditions > 0 || $practices > 0) 
		{
			$sum_total = $conditions + $acts + $practices;

			$conditions_closed	= $this->hasMany(UnsafeCondition::class)->where('status','2')->count();
			$acts_closed		= $this->hasMany(UnsafeAct::class)->where('status','2')->count();
			$practices_closed	= $this->hasMany(UnsafePractice::class)->where('status','2')->count();

			$sum_closed = $conditions_closed + $acts_closed + $practices_closed;

			$average = round((($sum_closed * 100) / $sum_total),2);
		}
		else
		{
			$average = '0';
		}

		return $average.'%';

	}
}
