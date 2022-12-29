<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabelDetailResource extends Model
{
	public $timestamps  = false;
	protected $fillable = 
	[
		'request_folio',
		'request_kind',
		'labels_idlabels',
	];

	public function label()
	{
		return $this->belongsTo(Label::class,'labels_idlabels','idlabels');
	}
}
