<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StaffDocuments extends Model
{
    protected $primaryKey = 'id';
	protected $fillable = 
	[
        'name',
        'path',
        'id_staff_employee'
    ];
}
