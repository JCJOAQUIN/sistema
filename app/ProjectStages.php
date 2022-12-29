<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectStages extends Model
{
    protected $fillable = 
	[
        'name',
        'description'
    ];
}
