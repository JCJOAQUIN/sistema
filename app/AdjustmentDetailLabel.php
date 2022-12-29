<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdjustmentDetailLabel extends Model
{
	protected $primaryKey = 'idadjustmentDetailLabel';
	public $timestamps    = false;
	protected $fillable   = 
	[
		'idlabels',
		'idadjustmentDetail'
	];

	public function label()
	{
		return $this->belongsTo(Label::class,'idlabels','idlabels');
	}
}
