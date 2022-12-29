<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PCDailyReportSignature extends Model
{
    protected $table    = 'pc_daily_report_signatures';
    protected $fillable = 
    [
        'name',
        'position',
        'pc_daily_report_id',
    ];
}
