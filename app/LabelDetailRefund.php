<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabelDetailRefund extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idlabelDetailRefund';
	protected $fillable   = 
	[
		'idlabels',
		'idRefundDetail',
	];

	public function label()
	{
		return $this->belongsTo(Label::class,'idlabels','idlabels');
	}
}
