<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabelDetailPurchase extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idlabelDetailPurchase';
	protected $fillable   = 
	[
		'idlabels',
		'idDetailPurchase',
	];

	public function label()
	{
		return $this->belongsTo(Label::class,'idlabels','idlabels');
	}
}
