<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CatMachinery extends Model
{
    protected $table = 'cat_machinery';
    protected $fillable = 
	[
		'name',
	];
}
