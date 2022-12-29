<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class COCAdvanceTypeTable extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idUpload',
		'costodirectodelaobra',
		'indirectodeobra',
		'costodirectoindirecto',
		'montototaldelaobra',
		'importeparafinanciamiento',
		'importeejercer1',
		'importeejercer2',
	];
}
