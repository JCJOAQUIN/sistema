<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResourceDetailTemp extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'concept',
		'amount',
		'idResourceTemp',
		'idAccAcc',
	];

	public function accounts()
	{
		return $this->belongsTo(Account::class,'idAccAcc','idAccAcc');
	}
}
