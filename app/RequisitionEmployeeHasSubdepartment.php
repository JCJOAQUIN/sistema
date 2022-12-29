<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequisitionEmployeeHasSubdepartment extends Model
{
    protected $fillable = 
    [
        'requisition_employee_id',
        'subdepartment_id'
    ];

    public function dataSubdepartment()
    {
        return $this->hasOne(Subdepartment::class,'id','subdepartment_id');
    }
}
