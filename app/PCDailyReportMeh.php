<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PCDailyReportMeh extends Model
{
    protected $table    = 'pc_daily_report_meh';
    protected $fillable = 
    [
        'quantity',
        'machinery_id',
        'pc_daily_report_id',
    ];

    public function machineries()
    {
        return $this->hasOne(CatMachinery::class,'id','machinery_id');
    }
}
