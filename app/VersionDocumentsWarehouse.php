<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VersionDocumentsWarehouse extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'iddocumentsWarehouse',
		'path',
		'idlot',
		'version',
	];
}
