<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PrenominaEmployee extends Model
{
    protected $table      = 'prenomina_employee';
    protected $primaryKey = null;
    public $incrementing  = false;
    public $timestamps    = false;
    protected $fillable = ['absence','extra_hours','holidays','sundays'];
}
