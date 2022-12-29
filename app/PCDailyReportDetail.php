<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class PCDailyReportDetail extends Model
{
    protected $table = 'pc_daily_report_details';
    protected $fillable = 
    [
        'contract_item_id',
        'quantity',
        'amount',
        'contractor_id',
        'area',
        'place_area',
        'num_ppt',
        'blueprint_id',
        'comments',
        'accumulated',
        'pc_daily_report_id',
    ];

    public function pcdrDocuments()
	{
		return $this->hasMany(PCDailyReportDocuments::class,'pcdr_details_id','id');
	}

    public function contract()
    {
        return $this->hasOne(CatContractItem::class,'id','contract_item_id');
    }

    public function contractor()
    {
        return $this->hasOne(Contractor::class,'id','contractor_id');
    }

    public function blueprint()
    {
        return $this->hasOne(Blueprints::class,'id','blueprint_id');
    }
}
