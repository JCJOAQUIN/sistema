<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PCDailyReportStaff extends Model
{
    protected $table    = 'pc_daily_report_staff';
    protected $fillable = 
    [
        'quantity',
        'industrial_staff_id',
        'hours',
        'pc_daily_report_id',
    ];

    public function staffIndustry()
    {
        return $this->hasOne(CatIndustrialStaff::class,'id','industrial_staff_id');
    }
}
