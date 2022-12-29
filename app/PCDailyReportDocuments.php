<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PCDailyReportDocuments extends Model
{
    protected $table    = 'pc_daily_report_details_documents';
    protected $fillable = 
    [
        'path',
        'pcdr_details_id',
    ];
}
