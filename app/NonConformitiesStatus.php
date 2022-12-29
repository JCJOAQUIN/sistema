<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NonConformitiesStatus extends Model
{
	protected $table = 'non_conformities_statuses';

	protected $fillable = [
		'id',
		'project_id',
		'wbs_id',
		'description',
		'date',
		'location',
		'process_area',
		'non_conformity_origin',
		'type_of_action',
		'action',
		'emited_by',
		'status',
		'nc_report_number',
		'close_date',
		'observations',
		'user_id',
		'created_at',
		'updated_at'
	];

	public function projectData()
	{
		return $this->hasOne(Project::class,'idproyect','project_id');
	}

	public function wbsData()
	{
		return $this->hasOne(CatCodeWBS::class,'id','wbs_id');
	}

	public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function documents()
	{
		return $this->hasMany(NonConformitiesStatusDocument::class,'non_conformities_status_id','id');
	}

	public function statusData()
	{
		switch ($this->status) 
		{
			case 1:
				return "Activo";
				break;

			case 2:
				return "En proceso";
				break;

			case 3:
				return "Finalizado";
				break;
			
			default:
				return "";
				break;
		}
	}

	public function typeAction()
	{
		switch ($this->status) 
		{
			case 1:
				return "No Conformidad";
				break;

			case 2:
				return "Acción Correctiva";
				break;

			case 3:
				return "Oportunidad de Mejora";
				break;
			
			default:
				return "";
				break;
		}
	}
}
