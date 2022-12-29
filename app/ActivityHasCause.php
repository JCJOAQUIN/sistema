<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityHasCause extends Model
{
    protected $fillable = 
    [
        'causes_code',
        'activity_id'
    ];
}
