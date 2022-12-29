<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabelDetailStationery extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idlabelDetailStationery';
	protected $fillable   = 
	[
		'idlabels',
		'idStatDetail',
	];

	public function label()
	{
		return $this->belongsTo(Label::class,'idlabels','idlabels');
	}
}
