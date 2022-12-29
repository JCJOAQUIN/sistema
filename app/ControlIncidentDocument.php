<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ControlIncidentDocument extends Model
{
    protected $fillable = 
    [
        'path',
        'user_id',
        'control_incident_id'
    ];
}
