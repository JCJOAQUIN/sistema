<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Functions\Files;

class News extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idnews';
	protected $fillable   = 
	[
		'title'
		,'details',
		'date',
		'path',
	];
}
