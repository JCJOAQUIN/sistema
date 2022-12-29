<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NonConformitiesStatusDocument extends Model
{
    protected $fillable = 
    [
        'path',
        'user_id',
        'non_conformities_status_id'
    ];
}
