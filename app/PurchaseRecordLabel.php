<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseRecordLabel extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'idLabel',
		'idPurchaseRecordDetail',
	];

	public function label()
	{
		return $this->hasOne(Label::class,'idlabels','idLabel');
	}
}
