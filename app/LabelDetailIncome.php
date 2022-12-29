<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabelDetailIncome extends Model
{
	public $timestamps    = false;
	protected $primaryKey = 'idlabelDetailIncome';
	protected $fillable   = 
	[
		'idlabels',
		'idincomeDetail',
	];

	public function label()
	{
		return $this->belongsTo(Label::class,'idlabels','idlabels');
	}
}
