<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PCDailyReport extends Model
{
    protected $table = 'pc_daily_report';

    protected $fillable   = 
	[
        'user_elaborate_id',
        'project_id',
        'contract_id',
        'date',
        'wbs_id',
        'weather_conditions_id',
        'discipline_id',
        'work_hours_from',
        'work_hours_to',
        'tm_internal_hours_from',
        'tm_internal_hours_to',
        'tm_internal_id',
        'tm_client_hours_from',
        'tm_client_hours_to',
        'tm_client_id',
        'comments',
        'status',
        'project',
        'package',
        'kind_doc',
        'name_file',
    ];

    public function elaborateUser()
	{
		return $this->hasOne(User::class,'id','user_elaborate_id');
	}

    public function wbs()
    {
        return $this->hasOne(CatCodeWBS::class,'id','wbs_id');
    }

    public function discipline()
    {
        return $this->hasOne(CatDiscipline::class,'id','discipline_id');
    }

    public function weather()
    {
        return $this->hasOne(CatWeatherConditions::class,'id','weather_conditions_id');
    }

    public function catTMC()
    {
        return $this->hasOne(CatTM::class,'id','tm_client_id');
    }

    public function reportProject()
	{
		return $this->hasOne(Project::class,'idproyect','project_id');
	}

    public function pcdrDetails()
	{
		return $this->hasMany(PCDailyReportDetail::class,'pc_daily_report_id','id');
	}

    public function pcdrMEH()
	{
		return $this->hasMany(PCDailyReportMeh::class,'pc_daily_report_id','id');
	}

    public function pcdrStaff()
	{
		return $this->hasMany(PCDailyReportStaff::class,'pc_daily_report_id','id');
	}

    public function pcdrSignatures()
	{
		return $this->hasMany(PCDailyReportSignature::class,'pc_daily_report_id','id');
	}

    public function noReport()
	{
		return $this->project.'-'.$this->package.'-'.$this->wbs->code.'-'.$this->discipline->indicator.'-'.$this->kind_doc.'-'.$this->id;
	}
}
