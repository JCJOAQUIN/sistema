<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequisitionEmployeeDocument extends Model
{
    protected $fillable = 
    [
        'name',
        'path',
        'requisition_employee_id',
    ];
}
