<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PreventiveRiskInspectionDetail extends Model
{
    public $table = 'preventive_risk_inspection_detail';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'preventive_risk_inspection_id',
        'category_id',
        'subcategory_id',
        'act',
        'severity',
        'hour',
        'discipline',
        'condition',
        'action',
        'observer',
        'responsible',
        'status',
        'dateend'
    ];

    public function category()
	{
		return $this->hasOne(AuditCategory::class, 'id', 'category_id');
	}

	public function subcategory()
	{
		return $this->hasOne(AuditSubcategory::class, 'id', 'subcategory_id');
	}

    public function preventive()
    {
        return $this->belongsTo(PreventiveRiskInspection::class, 'id', 'preventive_risk_inspection_id');
    }

    public function statusData()
    {
        switch ($this->status) 
        {
            case '0':
                return 'Abierto';
                break;

            case '1':
                return 'Cerrado';
                break;
            
            default:
                // code...
                break;
        }
    }

    public function actData()
    {
        switch ($this->act) 
        {
            case '1':
                return 'Acto';
                break;

            case '2':
                return 'Condición';
                break;
            
            default:
                // code...
                break;
        }
    }
}
