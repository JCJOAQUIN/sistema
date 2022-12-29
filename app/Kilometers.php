<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kilometers extends Model
{
    protected $fillable = 
	[
		'date_kilometer',
		'kilometer',
        'vehicles_id',
	];
}