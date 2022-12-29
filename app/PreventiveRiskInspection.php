<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PreventiveRiskInspection extends Model
{
    public $table = 'preventive_risk_inspection';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'project_id',
        'wbs_id',
        'contractor_id',
        'area',
        'date',
        'heading',
        'supervisor_name',
        'responsible_name',
        'observation'
    ];

    public function project()
	{
		return $this->hasOne(Project::class, 'idproyect', 'project_id');
	}

	public function codeWBS()
	{
		return $this->hasOne(CatCodeWBS::class, 'id', 'wbs_id');
	}

    public function contractorData()
	{
		return $this->hasOne(Contractor::class,'id','contractor_id');
	}

    public function detailInspection()
    {
        return $this->hasMany(PreventiveRiskInspectionDetail::class, 'preventive_risk_inspection_id','id');
    }

    public function user()
	{
		return $this->hasOne(User::class,'id', 'user_id');
	}

    
    public function countDangerousnessOneThirdSubcategory($id)
    {
        $details = $this->hasMany(PreventiveRiskInspectionDetail::class, 'preventive_risk_inspection_id','id')->where('severity','1/3')->where('subcategory_id',$id)->count();
        return $details;
    }

    public function countDangerousnessOneSubcategory($id)
    {
        $details = $this->hasMany(PreventiveRiskInspectionDetail::class, 'preventive_risk_inspection_id','id')->where('severity','1')->where('subcategory_id',$id)->count();
        return $details;
    }

    public function countDangerousnessThreeSubcategory($id)
    {
        $details = $this->hasMany(PreventiveRiskInspectionDetail::class, 'preventive_risk_inspection_id','id')->where('severity','3')->where('subcategory_id',$id)->count();
        return $details;
    }
}
