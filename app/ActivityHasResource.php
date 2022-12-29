<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityHasResource extends Model
{
    protected $fillable = 
    [
        'resource_code',
        'activity_id'
    ];
}
